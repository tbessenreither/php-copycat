<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use InvalidArgumentException;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;


class FileResolver
{

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

}
