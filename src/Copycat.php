<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use InvalidArgumentException;
use RuntimeException;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;
use Tbessenreither\PhpCopycat\Enum\CopyTargetEnum;
use Throwable;


class Copycat
{
    private string $projectRoot;

    public function __construct(
        private PackageInfo $packageInfo,
    ) {
        $this->projectRoot = explode('vendor', __DIR__)[0];
    }

    /**
     * Copies a file from the package to the specified target location in the project.
     * This method does not create directories if they do not exist, so the target directory must already exist before calling this method.
     */
    public function copy(CopyTargetEnum $target, string $file): void
    {
        try {
            $file = $this->resolveFile($file);

            echo 'copy ' . $file . ' to ' . $target->value . '';
            $this->performCopy($file, $this->getTargetDir($target));

        } catch (Throwable $e) {
            $this->logError('copy', $e);
        }
    }

    private function logError(string $method, Throwable $e): void
    {
        echo $method . " Error - " . $e->getMessage() . PHP_EOL;
    }

    private function resolveFile(string $file): string
    {
        $resolvedFile = null;
        if (file_exists($file) && is_file($file)) {
            $resolvedFile = realpath($file);
        }

        $relativePathPackage = $this->packageInfo->getPackagePath() . '/' . $file;
        if (file_exists($relativePathPackage) && is_file($relativePathPackage)) {
            $resolvedFile = realpath($relativePathPackage);
        }

        $relativePathAutoload = $this->packageInfo->getAutoloadPath() . '/' . $file;
        if (file_exists($relativePathAutoload) && is_file($relativePathAutoload)) {
            $resolvedFile = realpath($relativePathAutoload);
        }

        if ($resolvedFile === null) {
            throw new InvalidArgumentException('File not found: ' . $file);
        }

        if (!str_starts_with($resolvedFile, $this->packageInfo->getPackagePath() . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('Cannot access file outside of package scope: ' . $file);
        }

        return $resolvedFile;
    }

    private function performCopy(string $source, string $destinationDirectory): void
    {
        if (!file_exists($source) || !is_file($source)) {
            throw new InvalidArgumentException('Source file does not exist: ' . $source);
        }

        if (!file_exists($destinationDirectory) || !is_dir($destinationDirectory)) {
            throw new InvalidArgumentException('Destination directory does not exist: ' . $destinationDirectory);
        }

        $destination = rtrim($destinationDirectory, '/') . '/' . basename($source);

        if (!copy($source, $destination)) {
            throw new RuntimeException('Failed to copy file from ' . $source . ' to ' . $destination);
        }
    }

    private function getTargetDir(CopyTargetEnum $target): string
    {
        return $this->projectRoot . $target->value;
    }

}
