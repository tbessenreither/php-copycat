<?php declare(strict_types=1);

namespace Tbessenreither\Copycat;

use Tbessenreither\Copycat\Enum\CopyTargetEnum;
use Tbessenreither\Copycat\Interface\CopycatConfigInterface;
use Tbessenreither\Copycat\Interface\CopycatInterface;


class CopycatConfig implements CopycatConfigInterface
{

    public static function run(CopycatInterface $copycat): void
    {
        $copycat->copy(
            target: CopyTargetEnum::COPYCAT_CONFIG,
            file: 'config/copycat.json',
            overwrite: false,
            gitIgnore: false,
            createTargetDirectory: true,
        );
    }

}
