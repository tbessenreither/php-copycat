<?php declare(strict_types=1);

namespace Tbessenreither\Copycat\Service;

use RuntimeException;
use Tbessenreither\Copycat\Enum\CopyTargetEnum;


class ConfigLoader
{
	public static $configCache = null;

	public static function getWhitelistedNamespaces(): array|false
	{
		$config = self::getCopycatConfig();
		if (
			!isset($config['whitelistedNamespaces'])
			|| !is_array($config['whitelistedNamespaces'])
		) {
			return false;
		}

		return $config['whitelistedNamespaces'];

	}

	private static function getCopycatConfig(): array
	{
		if (self::$configCache !== null) {
			return self::$configCache;
		}

		$configFile = FileResolver::resolveConfigFile();

		$configContent = file_get_contents($configFile);
		$configContentDecoded = json_decode($configContent, true);
		if ($configContentDecoded === null) {
			throw new RuntimeException('Failed to decode copycat config file at ' . $configFile . '. JSON error: ' . json_last_error_msg());
		}

		self::$configCache = $configContentDecoded;
		return $configContentDecoded;
	}

}