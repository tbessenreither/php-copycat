<?php declare(strict_types=1);

namespace Tbessenreither\MultiLevelCache;

use Tbessenreither\FeatureFlagServiceClient\Bundle\FeatureFlagClientBundle;
use Tbessenreither\PhpCopycat\Enum\CopyTargetEnum;
use Tbessenreither\PhpCopycat\Interface\CopycatConfigInterface;
use Tbessenreither\PhpCopycat\Interface\CopycatInterface;


class CopycatConfig implements CopycatConfigInterface
{

    public static function run(CopycatInterface $copycat): void
    {
        $copycat->copy(
            target: CopyTargetEnum::PUBLIC ,
            file: 'src/CopycatConfig.php',
            gitIgnore: true,
        );

        $copycat->symfonyBundleAdd(
            bundleClassName: FeatureFlagClientBundle::class,
        );
    }

}
