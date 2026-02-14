<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Modifier;


class YamlModifier
{
    public const string INDENTATION = '    ';

    public static function indentation(int $level): string
    {
        return str_repeat(self::INDENTATION, max([0, $level]));
    }

}
