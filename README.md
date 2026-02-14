
# PHP Copycat


**PHP Copycat** is a utility package for automating file copying, JSON modifications, .gitignore management, and Symfony bundle registration in PHP projects. It is designed for flexible configuration and easy integration, allowing you to define custom copy, JSON, and project automation operations via configuration classes.

> **Smart Target Validation:**
> Copycat automatically checks if your project matches the expected system (e.g., Symfony, DDEV) for each operation. If the project does not match, the operation is skipped, preventing unwanted or unsafe changes.

---


## Features

### Current features

- Copy files to specific targets (e.g., DDEV commands, Symfony, public, etc.)
- Add or modify JSON values at any path in a target file
- Add entries to your project's `.gitignore` in a grouped, idempotent way
- Register Symfony bundles automatically in `config/bundles.php`
- **Smart system validation:** Only runs operations if your project matches the expected system for the target (e.g., Symfony, DDEV)
- Secure: prevents copying files from outside your package scope
- Easily integrate with Composer scripts for automation

### Planned Features

PHP Copycat is actively developed. Planned features include:
- Support for modifying yaml configuration files
- Supporting Laravel targets and system
- Adding / Modifying the .env.local file (and only this file).
- Echo of messages after execution (e.g., "Package [Packagename]: To use this package, do X, Y, Z...")
- do not overwrite flag for copy operations (e.g., only copy if file does not exist). Usefull for copying boilerplate files that the user may have modified after installation.

---

## Typical Workflow

On `composer install` or `composer update`, Copycat will:

- Copy required scripts (e.g., shell scripts, command files) to designated locations
- Update JSON configuration files with new or modified values
- Ensure your project is ready to use new features or settings from dependencies

---

## Installation

Install via Composer:

```sh
composer require tbessenreither/php-copycat
```

---

## Usage


### 1. Create a configuration class

Implement the `CopycatConfigInterface` in a class named `CopycatConfig`. This class **must** be located in the autoload root of your package (as defined in your `composer.json` autoload section) so it can be discovered and executed automatically.

**System-aware operations:**
When you call methods like `copy()`, `jsonAdd()`, or `symfonyBundleAdd()`, Copycat will first check if your project matches the expected system for the target (e.g., only copy Symfony files if your project is a Symfony app). If not, the operation is skipped and your project is left unchanged.


Example: [Examples/CopycatConfig.php](Examples/CopycatConfig.php)
This example demonstrates a full-featured Copycat configuration class. It shows how to:
- Copy files to a DDEV target
- Add and modify JSON values in a project file
- Add entries to .gitignore in a grouped way
- Register a Symfony bundle
- Register a service in Symfony's services.yaml

```php
<?php declare(strict_types=1);

namespace Tbessenreither\MultiLevelCache;

use Tbessenreither\MultiLevelCache\Bundle\MultiLevelCacheBundle;
use Tbessenreither\PhpCopycat\Copycat;
use Tbessenreither\PhpCopycat\Enum\CopyTargetEnum;
use Tbessenreither\PhpCopycat\Enum\JsonTargetEnum;
use Tbessenreither\PhpCopycat\Interface\CopycatConfigInterface;

class CopycatConfig implements CopycatConfigInterface
{
    public static function run(Copycat $copycat): void
    {
        /* DDEV specific configuration */
        $copycat->copy(
            target: CopyTargetEnum::DDEV_COMMANDS_WEB,
            file: 'ddev/commands/web/test-command.sh',
        );


        /* General JSON modifications */
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

        $copycat->gitIgnoreAdd(
            entries: [
                CopyTargetEnum::DDEV_COMMANDS_WEB->value . '/mlc-make',
                CopyTargetEnum::DDEV_COMMANDS_WEB->value . '/mlc-update',
            ]
        );

        /* Symfony specific configuration */
        $copycat->symfonyBundleAdd(
            bundleClassName: MultiLevelCacheBundle::class,
        );

        $copycat->symfonyAddServiceToYaml(
            Copycat::class,
            arguments: [
                '$packageInfo' => 'Tbessenreither\MultiLevelCache\Dto\PackageInfo',
            ],
        );
    }
}
```

---

### 2. Configure Composer scripts

Add the following to your `composer.json` to run Copycat automatically after install/update:

```json
"scripts": {
    "post-install-cmd": [
        "Tbessenreither\\PhpCopycat\\Runner::run"
    ],
    "post-update-cmd": [
        "Tbessenreither\\PhpCopycat\\Runner::run"
    ]
}
```

---


## API Reference

- `Copycat::copy(target, file)` — Copy a file to a specified target. See [src/Modifier/FileCopy.php](src/Modifier/FileCopy.php)
- `Copycat::jsonAdd(target, path, value)` — Add or modify a value in a JSON file at the given path. See [src/Modifier/JsonModifier.php](src/Modifier/JsonModifier.php)
- `Copycat::gitIgnoreAdd(entries)` — Add one or more entries to the project’s `.gitignore` file, grouped by your package namespace. See [src/Modifier/GitignoreModifier.php](src/Modifier/GitignoreModifier.php)
- `Copycat::symfonyBundleAdd(bundleClassName)` — Register a Symfony bundle in `config/bundles.php` (if the project is a Symfony app). See [src/Modifier/SymfonyModifier.php](src/Modifier/SymfonyModifier.php)
- `Copycat::symfonyAddServiceToYaml(serviceClass, arguments)` — Register a service in Symfony's `services.yaml` with optional constructor arguments. See [src/Modifier/SymfonyModifier.php](src/Modifier/SymfonyModifier.php)

---

## Example


See [Examples/CopycatConfig.php](Examples/CopycatConfig.php) for a full configuration example, including .gitignore and Symfony bundle registration.

---

## Project Structure

- `src/` — Main source code
    - `Copycat.php` — Main entry point
    - `Runner.php` — Composer script runner
    - `Modifier/` — File and JSON modification logic
    - `Enum/` — Target enums for copy and JSON operations
    - `Dto/` — Data transfer objects
    - `Interface/` — Configuration interface
- `Examples/` — Example configuration and usage

---


## Security & Safety

- The `copy` operation is protected: it will not allow copying files from outside your package scope, ensuring safe and predictable automation.
- **System validation:** Operations are only performed if your project matches the expected system for the target (e.g., Symfony, DDEV). This prevents accidental or unsafe changes in the wrong type of project.

---

## License

MIT
