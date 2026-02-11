<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Enum;


enum CopyTargetEnum: string
{
	case DDEV_COMMANDS_WEB = '.ddev/commands/web';
	case DDEV_COMMANDS_HOST = '.ddev/commands/host';
	case SYMFONY_BIN = 'bin';
	case SYMFONY_CONFIG_PACKAGES = 'config/packages';
	case SYMFONY_CONFIG_ROUTES = 'config/routes';
	case PUBLIC = 'public';

}