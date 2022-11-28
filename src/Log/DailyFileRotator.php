<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Log;

use DateTimeImmutable;

final class DailyFileRotator implements SchedulerFileRotatorInterface
{
    private const DEFAULT_DAYS = 14;
    private const DATE_FORMAT = 'Y-m-d';
    private const FILENAME_FORMAT = '{filename}-{date}';

    private int $days;

    private ?DateTimeImmutable $expectedDate = null;

    public function __construct(int $days = self::DEFAULT_DAYS)
    {
        $this->days = $days;
    }

    public function setExpectedDate(?DateTimeImmutable $expectedDate): self
    {
        $this->expectedDate = $expectedDate;

        return $this;
    }

    /**
     * Rotates files.
     *
     * @param string $file The log file for rotation.
     */
    public function rotateFile(string $file): void
    {
        $this->removeOldFiles($file);
    }

    /**
     * Checks whether the file should be rotated.
     *
     * @param string $file The log file for rotation.
     *
     * @return bool Whether you should rotate the file.
     */
    public function shouldRotateFile(string $file): bool
    {
        // on the first record written, if the log is new, we should rotate (once per day)
        return !file_exists($file);
    }

    public function expectedFile(string $file): string
    {
        return str_replace(
            [
                '{filename}',
                '{date}'
            ],
            [
                $this->fileName($file),
                $this->expectedDate
                    ? $this->expectedDate->format(self::DATE_FORMAT)
                    : (new DateTimeImmutable())->format(self::DATE_FORMAT)
            ],
            $this->path($file) . '/' . self::FILENAME_FORMAT . '.' . $this->fileExtension($file)
        );
    }

    private function getGlobPattern(string $file): string
    {
        return str_replace(
            [
                '{filename}',
                '{date}'
            ],
            [
                $this->fileName($file),
                '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]*'
            ],
            $this->path($file) . '/' . self::FILENAME_FORMAT . '.' . $this->fileExtension($file)
        );
    }

    private function removeOldFiles(string $file): void
    {
        $files = glob($this->getGlobPattern($file));

        if ($this->days >= count($files)) {
            // no files to remove
            return;
        }

        // Sorting the files by name to remove the older ones
        usort($files, function ($a, $b) {
            return strcmp($b, $a);
        });

        foreach (array_slice($files, $this->days) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function path(string $file): string
    {
        return pathinfo($file, PATHINFO_DIRNAME);
    }

    private function fileName(string $file): string
    {
        return pathinfo($file, PATHINFO_FILENAME);
    }

    private function fileExtension(string $file): string
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}
