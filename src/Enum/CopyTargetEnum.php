<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Enum;


enum CopyTargetEnum: string
{
	case DDEV_COMMANDS_WEB = '.ddev/commands/web';
	case DDEV_COMMANDS_HOST = '.ddev/commands/host';

}