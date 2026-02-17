<?php declare(strict_types=1);

namespace Tbessenreither\Copycat;

use Tbessenreither\Copycat\Enum\EnvTargetEnum;
use Tbessenreither\Copycat\Enum\JsonTargetEnum;
use Tbessenreither\Copycat\Interface\CopycatConfigInterface;
use Tbessenreither\Copycat\Interface\CopycatInterface;
use Tbessenreither\Copycat\Service\ConfigLoader;


class CopycatConfig implements CopycatConfigInterface
{

    public static function run(CopycatInterface $copycat): void
    {
        $config = ConfigLoader::getCopycatConfig();
        $copycat->jsonAdd(
            target: JsonTargetEnum::COMPOSER_JSON,
            path: 'extra.copycat',
            value: $config,
            overwrite: false,
        );

        $copycat->envAdd(
            target: EnvTargetEnum::DOT_EXAMPLE,
            entries: [
                'COPYCAT_EXAMPLE_VARIABLE' => 'example_value',
                'FFS_SCOPE' => 'example_scope',
                'Number' => '12345',
                'SpecialChars' => 'value with spaces and special chars !@#$%^&*()',
                'QuotedValue' => 'value with "quotes" and $dollar signs',
                'newline' => "value with\nnewlines",
                '$weirdkey' => 'value with weird key',
                'varValue' => '$HOME/somepath',
            ],
            overwrite: true,
        );
    }

}
