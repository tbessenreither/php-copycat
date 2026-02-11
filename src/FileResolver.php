<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use InvalidArgumentException;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;


class FileResolver
{
    private static array $bufferedFiles = [];

    public static function resolve(PackageInfo $packageInfo, string $file, bool $enforceScope = true): string
    {
        $resolvedFile = null;
        if (file_exists($file) && is_file($file)) {
            $resolvedFile = realpath($file);
        }

        $relativePathPackage = $packageInfo->getPackagePath() . '/' . $file;
        if (file_exists($relativePathPackage) && is_file($relativePathPackage)) {
            $resolvedFile = realpath($relativePathPackage);
        }

        $relativePathAutoload = $packageInfo->getAutoloadPath() . '/' . $file;
        if (file_exists($relativePathAutoload) && is_file($relativePathAutoload)) {
            $resolvedFile = realpath($relativePathAutoload);
        }

        if ($resolvedFile === null) {
            throw new InvalidArgumentException('File not found: ' . $file);
        }

        if ($enforceScope === true && !str_starts_with($resolvedFile, $packageInfo->getPackagePath() . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('Cannot access file outside of package scope: ' . $resolvedFile);
        }

        return $resolvedFile;
    }

    public static function loadFile(string $file): string
    {
        if (!isset(self::$bufferedFiles[$file])) {

            echo "Loading file: $file" . PHP_EOL;
            if (!file_exists($file) || !is_file($file)) {
                throw new InvalidArgumentException('File not found: ' . $file);
            }
            $fileData = file_get_contents($file);

            self::$bufferedFiles[$file] = $fileData;
        }

        return self::$bufferedFiles[$file];
    }

    public static function storeFileModification(string $file, string $content): void
    {
        echo "Storing modifications for: $file" . PHP_EOL;
        self::$bufferedFiles[$file] = $content;
    }

    public static function writeBufferedFilesToDisk(): void
    {
        foreach (self::$bufferedFiles as $file => $content) {
            echo "Writing file to disk: $file" . PHP_EOL;
            file_put_contents($file, $content);
        }
        self::$bufferedFiles = [];
    }

}
