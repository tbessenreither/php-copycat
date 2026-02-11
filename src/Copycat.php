<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use Tbessenreither\PhpCopycat\Dto\PackageInfo;
use Tbessenreither\PhpCopycat\Enum\CopyTargetEnum;
use Tbessenreither\PhpCopycat\Enum\JsonTargetEnum;
use Tbessenreither\PhpCopycat\Modifier\FileCopy;
use Tbessenreither\PhpCopycat\Modifier\JsonModifier;
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
            $file = FileResolver::resolve(
                packageInfo: $this->packageInfo,
                file: $file,
            );

            FileCopy::copy(
                source: $file,
                destinationDirectory: $this->getTargetDir($target),
            );

        } catch (Throwable $e) {
            $this->logError('copy', $e);
        }
    }

    public function jsonAdd(JsonTargetEnum $target, string $path, mixed $value): void
    {
        try {
            $file = FileResolver::resolve(
                packageInfo: $this->packageInfo,
                file: $target->value,
                enforceScope: false,
            );

            JsonModifier::add(
                file: $file,
                path: $path,
                value: $value,
            );

        } catch (Throwable $e) {
            $this->logError('jsonAdd', $e);
        }
    }

    private function logError(string $method, Throwable $e): void
    {
        echo $method . " Error - " . $e->getMessage() . PHP_EOL;
    }

    private function getTargetDir(CopyTargetEnum $target): string
    {
        return $this->projectRoot . $target->value;
    }

}
