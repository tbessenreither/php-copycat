<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use RuntimeException;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;
use Tbessenreither\PhpCopycat\Enum\CopyTargetEnum;
use Tbessenreither\PhpCopycat\Enum\JsonTargetEnum;
use Tbessenreither\PhpCopycat\Enum\KnownSystemsEnum;
use Tbessenreither\PhpCopycat\Modifier\FileCopy;
use Tbessenreither\PhpCopycat\Modifier\GitignoreModifier;
use Tbessenreither\PhpCopycat\Modifier\JsonModifier;
use Tbessenreither\PhpCopycat\Modifier\SymfonyModifier;
use Throwable;


class Copycat
{
    private string $projectRoot;
    /**
     * @var array<string, string>
     */
    private array $bufferedFiles = [];

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
            SystemValidator::validateSystem($this->packageInfo, $target->getSystem());

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
            SystemValidator::validateSystem($this->packageInfo, $target->getSystem());

            $file = FileResolver::resolveInProject(
                packageInfo: $this->packageInfo,
                file: $target->value,
            );

            $jsonModified = JsonModifier::add(
                fileContent: FileResolver::loadFile($file),
                path: $path,
                value: $value,
            );

            FileResolver::storeFileModification($file, $jsonModified);

        } catch (Throwable $e) {
            $this->logError('jsonAdd', $e);
        }
    }

    /**
     * Adds one or more entries to the .gitignore file in project root. If the .gitignore file does not exist, it will be created.
     * @param string|string[] $entry
     * @return void
     */
    public function gitIgnoreAdd(string|array $entries): void
    {
        if (!is_array($entries)) {
            $entries = [$entries];
        }
        try {
            $file = FileResolver::resolveInProject(
                packageInfo: $this->packageInfo,
                file: '.gitignore',
                createIfNotExists: true,
            );

            $modifiedContent = GitignoreModifier::add(
                fileContent: FileResolver::loadFile($file),
                entries: $entries,
                groupName: $this->packageInfo->getNamespace(),
            );

            FileResolver::storeFileModification($file, $modifiedContent);

        } catch (Throwable $e) {
            $this->logError('gitIgnoreAdd', $e);
        }
    }

    public function symfonyBundleAdd(string $bundleClassName): void
    {
        try {
            SystemValidator::validateSystem($this->packageInfo, KnownSystemsEnum::SYMFONY);

            $file = FileResolver::resolveInProject(
                packageInfo: $this->packageInfo,
                file: 'config/bundles.php',
            );

            $modifiedContent = SymfonyModifier::addToBundle(
                fileContent: FileResolver::loadFile($file),
                bundleClassName: $bundleClassName,
            );

            FileResolver::storeFileModification($file, $modifiedContent);

        } catch (Throwable $e) {
            $this->logError('symfonyBundleAdd', $e);
        }
    }

    public function symfonyAddServiceToYaml(string $serviceClass, array $arguments = []): void
    {
        try {
            SystemValidator::validateSystem($this->packageInfo, KnownSystemsEnum::SYMFONY);

            $file = FileResolver::resolveInProject(
                packageInfo: $this->packageInfo,
                file: 'config/services.yaml',
            );

            $modifiedContent = SymfonyModifier::addServiceToYaml(
                fileContent: FileResolver::loadFile($file),
                serviceClass: $serviceClass,
                arguments: $arguments,
            );

            FileResolver::storeFileModification($file, $modifiedContent);

        } catch (Throwable $e) {
            $this->logError('symfonyAddServiceToYaml', $e);
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
