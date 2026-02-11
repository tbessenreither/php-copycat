# PHP Copycat

A utility package for automating file copying and JSON modifications in PHP projects. Designed for flexible configuration and integration, Copycat enables you to define custom copy and JSON operations via configuration classes.


## Main Use Case

**PHP Copycat** is designed to automate the process of copying scripts and updating configuration values in your parent project whenever you install or update packages. By integrating Copycat with Composer scripts, you ensure that essential files and configuration changes are applied automatically, reducing manual steps and keeping your project up-to-date.

### Typical Workflow
- On `composer install` or `composer update`, Copycat runs and:
    - Copies required scripts (e.g., shell scripts, command files) to designated locations.
    - Updates JSON configuration files with new or modified values.
    - Ensures your project is ready to use new features or settings from dependencies.

- Copy files to specific targets
- Add or modify JSON values at any path
- Easily integrate with Composer scripts

## Security

The `copy` operation is protected: it will not allow copying files from outside your package scope, ensuring safe and predictable automation.
- Copy files to specific targets
- Add or modify JSON values at any path
- Easily integrate with Composer scripts

## Installation

Install via Composer:

```
composer require tbessenreither/php-copycat
```

## Usage


1. **Create a configuration class** implementing the `CopycatConfigInterface`.

> **Important:** The `CopycatConfig` class must be located in the autoload root of your package (as defined in your `composer.json` autoload section) so it can be discovered and executed automatically.

Example: [Examples/CopycatConfig.php](Examples/CopycatConfig.php)

```php
<?php declare(strict_types=1);

namespace YourNamespace;

use Tbessenreither\PhpCopycat\Copycat;
use Tbessenreither\PhpCopycat\Enum\CopyTargetEnum;
use Tbessenreither\PhpCopycat\Enum\JsonTargetEnum;
use Tbessenreither\PhpCopycat\Interface\CopycatConfigInterface;

class CopycatConfig implements CopycatConfigInterface
{
    public static function run(Copycat $copycat): void
    {
        $copycat->copy(
            target: CopyTargetEnum::DDEV_COMMANDS_WEB,
            file: 'ddev/commands/web/test-command.sh',
        );

        $copycat->jsonAdd(
            target: JsonTargetEnum::TEST,
            path: 'items',
            value: ['item4', 'item5']
        );
        $copycat->jsonAdd(
            target: JsonTargetEnum::TEST,
            path: 'config',
            value: [
                'setting1' => 'value1 ' . time(),
                'setting2' => 'value2 ' . time(),
            ],
        );
        $copycat->jsonAdd(
            target: JsonTargetEnum::TEST,
            path: 'config.setting3',
            value: 'value3 ' . time(),
        );
        $copycat->jsonAdd(
            target: JsonTargetEnum::TEST,
            path: 'nested.level1.level2.level3',
            value: 'nested value ' . time(),
        );
    }
}
```

2. **Configure Composer scripts** to run Copycat automatically after install/update.

Add the following to your `composer.json`:

```
"scripts": {
    "post-install-cmd": [
        "Tbessenreither\\PhpCopycat\\Runner::run"
    ],
    "post-update-cmd": [
        "Tbessenreither\\PhpCopycat\\Runner::run"
    ]
}
```

## API Reference

- `Copycat::copy(target, file)` — Copy a file to a specified target.
- `Copycat::jsonAdd(target, path, value)` — Add or modify a value in a JSON file at the given path.

## Example
See [Examples/CopycatConfig.php](Examples/CopycatConfig.php) for a full configuration example.

## License
MIT
