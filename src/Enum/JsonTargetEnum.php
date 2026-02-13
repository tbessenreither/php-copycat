<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Enum;


enum JsonTargetEnum: string
{
    case TEST = 'src/test.json';

    public function getSystem(): ?KnownSystemsEnum
    {
        return null;
    }

}
