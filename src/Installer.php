<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;

use ReflectionClass;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;
use Tbessenreither\PhpCopycat\Interface\CopycatConfigInterface;


class Installer
{

    public static function run(): void
    {
        echo "You are using the deprecated Installer class. Please use Runner::run() instead." . PHP_EOL;
        Runner::run();
    }

}
