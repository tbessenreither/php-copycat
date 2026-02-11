<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat;


class Installer
{

	public static function run(): void
	{
		echo str_repeat(PHP_EOL, 5);
		echo "Copycat script has been executed" . PHP_EOL;
		echo str_repeat(PHP_EOL, 5);
	}

}