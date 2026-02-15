<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Modifier;

use InvalidArgumentException;
use RuntimeException;


class FileCopy
{

    public static function copy(string $source, string $destinationDirectory, bool $overwrite = true): void
    {
        if (!file_exists($source) || !is_file($source)) {
            throw new InvalidArgumentException('Source file does not exist: ' . $source);
        }

        if (!file_exists($destinationDirectory) || !is_dir($destinationDirectory)) {
            throw new InvalidArgumentException('Destination directory does not exist: ' . $destinationDirectory);
        }

        $destination = rtrim($destinationDirectory, '/') . '/' . basename($source);

        if (!$overwrite && file_exists($destination)) {
            throw new RuntimeException('Destination file already exists: ' . $destination);
        }

        if (!copy($source, $destination)) {
            throw new RuntimeException('Failed to copy file from ' . $source . ' to ' . $destination);
        }
    }

    public static function remove(string $source, string $destinationDirectory): void
    {
        if (!file_exists($destinationDirectory) || !is_dir($destinationDirectory)) {
            throw new InvalidArgumentException('Destination directory does not exist: ' . $destinationDirectory);
        }

        $destination = rtrim($destinationDirectory, '/') . '/' . basename($source);

        if (!file_exists($destination) || !is_file($destination)) {
            throw new InvalidArgumentException('Destination file does not exist: ' . $destination);
        }

        if (!unlink($destination)) {
            throw new RuntimeException('Failed to remove file at ' . $destination);
        }
    }

}
