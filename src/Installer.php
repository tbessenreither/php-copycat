<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use ReflectionClass;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;
use Tbessenreither\PhpCopycat\Interface\CopycatConfigInterface;


class Installer
{

    public static function run(): void
    {
        echo str_repeat(PHP_EOL, 2);
        echo "Copycat script has been executed" . PHP_EOL;
        self::runCopycat();
        echo str_repeat(PHP_EOL, 2);
    }

    private static function runCopycat(): void
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
        $vendorDir = explode('vendor', $currentDir)[0] . 'vendor';

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
                        autoloadPath: $packageDirectory . '/' . $path,
                        packagePath: $packageDirectory,
                    );
                }
            }
        }

        return $namespaces;
    }

}
