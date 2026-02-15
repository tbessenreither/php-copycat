<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use ReflectionClass;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;
use Tbessenreither\PhpCopycat\Interface\CopycatConfigInterface;
use Tbessenreither\PhpCopycat\Service\Copycat;
use Tbessenreither\PhpCopycat\Service\CopycatReverse;
use Tbessenreither\PhpCopycat\Service\FileResolver;


class Runner
{

    public static function run(Event|PackageEvent $event): void
    {
        echo str_repeat(PHP_EOL, 2);
        echo "Running PHP Copycat..." . PHP_EOL;
        if ($event instanceof Event) {
            self::onInstallOrUpdate();
        } elseif ($event instanceof PackageEvent) {
            foreach ($event->getOperations() as $operation) {
                if ($operation->getOperationType() === 'uninstall') {
                    self::onUninstall($operation);
                } else {
                    echo "Operation type " . $operation->getOperationType() . " not supported." . PHP_EOL;
                }
            }
        }
        FileResolver::writeBufferedFilesToDisk();
        echo PHP_EOL . "PHP Copycat finished." . PHP_EOL;
        echo str_repeat(PHP_EOL, 2);
    }

    private static function onUninstall(OperationInterface $operation): void
    {
        $packageInfoString = $operation->show(false);
        //get string between <info> and </info>
        preg_match('/<info>(.*?)<\/info>/', $packageInfoString, $matches);
        if (count($matches) < 2) {
            echo "Could not extract package info from string: " . $packageInfoString . PHP_EOL;

            return;
        }
        $packageInfoStringCleaned = $matches[1];
        $packageInfoStringCleaned = trim($packageInfoStringCleaned);

        $namespaces = self::getInstalledNamespaces();
        $packageInfo = null;
        foreach ($namespaces as $namespace) {
            if ($namespace->getComposerName() === $packageInfoStringCleaned) {
                $packageInfo = $namespace;
                break;
            }
        }
        if ($packageInfo === null) {
            echo "Could not find affected namespace for package: " . $packageInfoStringCleaned . PHP_EOL;

            return;
        }

        $copycatInstance = new CopycatReverse(
            packageInfo: $packageInfo,
        );
        $copycatClass = $packageInfo->getNamespace() . '\\CopycatConfig';

        if (!class_exists($copycatClass)) {
            echo "No CopycatConfig class found for namespace " . $packageInfo->getNamespace() . ", skipping uninstall operations." . PHP_EOL;

            return;
        }

        $reflectionClass = new ReflectionClass($copycatClass);
        if (!$reflectionClass->implementsInterface(CopycatConfigInterface::class)) {
            echo "CopycatConfig class for namespace " . $packageInfo->getNamespace() . " does not implement CopycatConfigInterface, skipping uninstall operations." . PHP_EOL;

            return;
        }

        echo "Reverting copycat for namespace " . $packageInfo->getNamespace() . PHP_EOL;
        $copycatClass::run($copycatInstance);
    }

    private static function onInstallOrUpdate(): void
    {
        $namespaces = self::getInstalledNamespaces();

        foreach ($namespaces as $packageInfo) {
            $copycatInstance = new Copycat(
                packageInfo: $packageInfo,
            );

            $copycatClass = $packageInfo->getNamespace() . '\\CopycatConfig';

            if (!class_exists($copycatClass)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($copycatClass);
            if (!$reflectionClass->implementsInterface(CopycatConfigInterface::class)) {
                continue;
            }

            echo "Running copycat for namespace " . $packageInfo->getNamespace() . PHP_EOL;
            $copycatClass::run($copycatInstance);
        }
    }

    /**
     * @return PackageInfo[]
     */
    private static function getInstalledNamespaces(): array
    {
        $currentDir = __DIR__;
        $projectRootDir = explode('vendor', $currentDir)[0];
        $vendorDir = $projectRootDir . 'vendor';

        //itterate over all folders in vendor, read the composer.json and extract the namespaces from the autoload section
        $namespaces = [];
        foreach (scandir($vendorDir) as $vendorFolder) {
            if (
                $vendorFolder === '.'
                || $vendorFolder === '..'
                || !is_dir($vendorDir . '/' . $vendorFolder)
            ) {
                continue;
            }

            foreach (scandir($vendorDir . '/' . $vendorFolder) as $packageFolder) {
                if (
                    $packageFolder === '.'
                    || $packageFolder === '..'
                    || !is_dir($vendorDir . '/' . $vendorFolder . '/' . $packageFolder)
                ) {
                    continue;
                }
                $packageFolder = rtrim($packageFolder, '/');

                $packageDirectory = $vendorDir . '/' . $vendorFolder . '/' . $packageFolder;
                $packageDirectory = rtrim($packageDirectory, '/');

                $composerFile = $packageDirectory . '/composer.json';

                if (!file_exists($composerFile)) {
                    echo "No composer.json found in " . $composerFile . PHP_EOL;
                    continue;
                }

                $composerContent = file_get_contents($composerFile);
                $composerContentDecoded = json_decode($composerContent, true);
                if (
                    !isset($composerContentDecoded['autoload'])
                    || !isset($composerContentDecoded['autoload']['psr-4'])
                ) {
                    continue;
                }

                foreach ($composerContentDecoded['autoload']['psr-4'] as $namespace => $path) {
                    if (is_array($path)) {
                        if (count($path) === 1) {
                            $path = $path[0];
                        } else {
                            echo "Multiple paths for namespace " . $namespace . " in package " . $vendorFolder . '/' . $packageFolder . PHP_EOL;
                            continue;
                        }
                    }
                    $path = rtrim($path, '/');
                    $namespaces[] = new PackageInfo(
                        namespace: $namespace,
                        projectPath: $projectRootDir,
                        autoloadPath: $packageDirectory . '/' . $path,
                        packagePath: $packageDirectory,
                        composerName: $vendorFolder . '/' . $packageFolder,
                    );
                }
            }
        }

        return $namespaces;
    }

}
