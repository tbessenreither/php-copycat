<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Service;

use RuntimeException;
use Tbessenreither\PhpCopycat\Dto\PackageInfo;
use Tbessenreither\PhpCopycat\Enum\KnownSystemsEnum;


class SystemValidator
{

    public static function validateSystem(PackageInfo $packageInfo, ?KnownSystemsEnum $system): void
    {
        if ($system === null) {
            return;
        }

        if (!self::checkForSystem(packageInfo: $packageInfo, system: $system)) {
            throw new RuntimeException('The current project does not appear to be a ' . $system->value . ' project. Aborting operation.');
        }
    }

    private static function checkForSystem(PackageInfo $packageInfo, ?KnownSystemsEnum $system): bool
    {
        if ($system === null) {
            return false;
        }

        $indicatorFile = $system->getIndicatorFile();

        if ($system === KnownSystemsEnum::DDEV) {
            return file_exists($packageInfo->getProjectPath() . $indicatorFile) && is_dir($packageInfo->getProjectPath() . $indicatorFile);
        } elseif ($system === KnownSystemsEnum::SYMFONY) {
            return file_exists($packageInfo->getProjectPath() . $indicatorFile) && is_file($packageInfo->getProjectPath() . $indicatorFile);
        } elseif ($system === KnownSystemsEnum::GIT) {
            return file_exists($packageInfo->getProjectPath() . $indicatorFile) && is_dir($packageInfo->getProjectPath() . $indicatorFile);
        } else {
            throw new RuntimeException('Unknown system: ' . $system->value);
        }
    }

}
