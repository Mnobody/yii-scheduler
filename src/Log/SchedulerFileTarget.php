<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Log;

/**
 * This class is overridden to allow for daily rotation of log files and to clean up standard message formatting.
 * Original class: Yiisoft\Log\Target\File\FileTarget
 */

use RuntimeException;
use DateTimeImmutable;
use Yiisoft\Log\Target;
use Yiisoft\Log\Message;
use Yiisoft\Files\FileHelper;
use Yiisoft\Log\Target\File\FileRotatorInterface;

use function chmod;
use function clearstatcache;
use function dirname;
use function error_get_last;
use function fclose;
use function file_exists;
use function file_put_contents;
use function flock;
use function fwrite;
use function sprintf;
use function strlen;

use const FILE_APPEND;
use const LOCK_EX;
use const LOCK_UN;

/**
 * FileTarget records log messages in a file.
 *
 * The log file is specified via {@see FileTarget::$logFile}.
 *
 * If {@see FileRotator} is used and the size of the log file exceeds {@see FileRotator::$maxFileSize},
 * a rotation will be performed, which renames the current log file by suffixing the file name with '.1'.
 * All existing log files are moved backwards by one place, i.e., '.2' to '.3', '.1' to '.2', and so on.
 * If compression is enabled {@see FileRotator::$compressRotatedFiles}, the rotated files will be compressed
 * into the '.gz' format. The property {@see FileRotator::$maxFiles} specifies how many history files to keep.
 */
final class SchedulerFileTarget extends Target
{
    /**
     * @var string The log file path. If not set, it will use the "/tmp/app.log" file.
     * The directory containing the log files will be automatically created if not existing.
     */
    private string $logFile;

    /**
     * @var int The permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    private int $dirMode;

    /**
     * @var int|null The permission to be set for newly created log files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    private ?int $fileMode;

    private ?int $fileOwner;
    private ?int $fileGroup;

    private ?FileRotatorInterface $rotator;

    /**
     * @param string $logFile The log file path. If not set, it will use the "/tmp/app.log" file.
     * @param FileRotatorInterface|null $rotator The instance that takes care of rotating files.
     * @param int $dirMode The permission to be set for newly created directories.
     * @param int|null $fileMode The permission to be set for newly created log files.
     */
    public function __construct(
        string $logFile = '/tmp/app.log',
        FileRotatorInterface $rotator = null,
        int $dirMode = 0775,
        int $fileMode = null,
        int $fileOwner = null,
        int $fileGroup = null,
    ) {
        $this->logFile = $logFile;
        $this->rotator = $rotator;
        $this->dirMode = $dirMode;
        $this->fileMode = $fileMode;
        $this->fileOwner = $fileOwner;
        $this->fileGroup = $fileGroup;
        parent::__construct();

        $this->clearDefaultFormatting();
    }

    protected function clearDefaultFormatting(): void
    {
        /** @var Message $message */
        $this->setFormat(function ($message, $commonContext) {
            return rtrim($message->message());
        });
    }

    protected function getDate(Message $message): DateTimeImmutable
    {
        $timestamp = (string) $message->context('time', microtime(true));

        $format = match (true) {
            str_contains($timestamp, '.') => 'U.u',
            str_contains($timestamp, ',') => 'U,u',
            default => 'U',
        };

        return DateTimeImmutable::createFromFormat($format, $timestamp);
    }

    protected function getExpectedDate(): ?DateTimeImmutable
    {
        foreach ($this->getMessages() as $message) {
            // return date for first message
            return $this->getDate($message);
        }

        return null;
    }

    protected function export(): void
    {
        $this->rotator->setExpectedDate($this->getExpectedDate());

        $file = $this->rotator->expectedFile($this->logFile);

        $logPath = dirname($file);
        if (!file_exists($logPath)) {
            FileHelper::ensureDirectory($logPath, $this->dirMode);
        }

        /**
         * todo: refactoring in future
         * All messages are concatenated, so we will probably have cases with wrong distribution at the beginning of the day.
         * Ex. log message with date 2022-11-28 00:01 can go to file {filename}-2022-11-27.log
         * Leave it as is for now.
         */
        $text = $this->formatMessages("\n");
        $pointer = FileHelper::openFile($file, 'ab');
        flock($pointer, LOCK_EX);

        if ($this->rotator !== null) {
            // clear stat cache to ensure getting the real current file size and not a cached one
            // this may result in rotating twice when cached file size is used on subsequent calls
            clearstatcache();
        }

        if ($this->rotator !== null && $this->rotator->shouldRotateFile($file)) {
            flock($pointer, LOCK_UN);
            fclose($pointer);
            $this->rotator->rotateFile($this->logFile);
            $writeResult = file_put_contents($file, $text, FILE_APPEND | LOCK_EX);
        } else {
            $writeResult = fwrite($pointer, $text);
            flock($pointer, LOCK_UN);
            fclose($pointer);
        }

        $this->checkWrittenResult($writeResult, $text);

        if ($this->fileMode !== null) {
            chmod($file, $this->fileMode);
        }

        if ($this->fileOwner !== null) {
            chown($file, $this->fileOwner);
        }

        if ($this->fileGroup !== null) {
            chgrp($file, $this->fileGroup);
        }
    }

    /**
     * Checks the written result.
     *
     * @param false|int $writeResult The number of bytes written to the file, or FALSE if an error occurs.
     * @param string $text The text written to the file.
     *
     * @throws RuntimeException For unable to export log through file.
     */
    private function checkWrittenResult(false|int $writeResult, string $text): void
    {
        if ($writeResult === false) {
            throw new RuntimeException(sprintf(
                'Unable to export log through file: %s',
                error_get_last()['message'] ?? '',
            ));
        }

        $textSize = strlen($text);

        if ($writeResult < $textSize) {
            throw new RuntimeException(sprintf(
                'Unable to export whole log through file. Wrote %d out of %d bytes.',
                $writeResult,
                $textSize,
            ));
        }
    }
}
