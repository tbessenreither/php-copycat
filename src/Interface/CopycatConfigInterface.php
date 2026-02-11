<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Interface;

use Tbessenreither\PhpCopycat\Copycat;


interface CopycatConfigInterface
{

    public static function run(Copycat $copycat): void;

}
