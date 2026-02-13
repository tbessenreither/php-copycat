<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Enum;


enum KnownSystemsEnum: string
{
    case SYMFONY = 'symfony';
    case DDEV = 'ddev';

    public function getIndicatorFile(): string
    {
        return match ($this) {
            self::SYMFONY => '/config/bundles.php',
            self::DDEV    => '/.ddev',
        };
    }

}
