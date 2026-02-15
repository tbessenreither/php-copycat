<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Modifier;

use ReflectionClass;
use RuntimeException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;


class SymfonyModifier
{

    public static function addToBundle(string $fileContent, string $bundleClassName): string
    {
        self::checkIfBundleClassIsValid($bundleClassName);

        $lines = explode(PHP_EOL, $fileContent);


        // Find the line with "return ["
        $returnLineIndex = array_search('return [', $lines, true);
        if ($returnLineIndex === false) {
            throw new RuntimeException('Could not find "return [" in bundles.php file');
        }
        $endOfReturnLineIndex = array_search('];', $lines, true);
        if ($endOfReturnLineIndex === false) {
            throw new RuntimeException('Could not find "];" in bundles.php file');
        }

        //get the indentation from the line after "return ["
        $indentation = '';
        if (isset($lines[$returnLineIndex + 1])) {
            preg_match('/^(\s*)/', $lines[$returnLineIndex + 1], $matches);
            $indentation = $matches[1] ?? '';
        }

        $bundleLine = $indentation . $bundleClassName . "::class => ['all' => true],";
        if (in_array($bundleLine, $lines, true)) {
            throw new RuntimeException('Bundle ' . $bundleClassName . ' is already registered in bundles.php, skipping.');
        }


        //find the position between $returnLineIndex and $endOfReturnLineIndex where the new bundle should be inserted, so that the bundles are sorted alphabetically.
        $insertIndex = $returnLineIndex + 1;
        for ($i = $returnLineIndex + 1; $i < $endOfReturnLineIndex; $i++) {
            if (strcmp($lines[$i], $bundleLine) > 0) {
                $insertIndex = $i;
                break;
            }
        }
        // Insert the new bundle line at the correct position
        array_splice($lines, $insertIndex, 0, $bundleLine);

        return implode(PHP_EOL, $lines);
    }

    public static function removeFromBundle(string $fileContent, string $bundleClassName): string
    {
        $lines = explode(PHP_EOL, $fileContent);

        $bundleLine = null;
        foreach ($lines as $lineKey => $line) {
            if (strpos(trim($line), $bundleClassName . "::class") === 0) {
                unset($lines[$lineKey]);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    public static function addServiceToYaml(string $fileContent, string $serviceClass, array $arguments = []): string
    {
        $yamlLines = explode(PHP_EOL, $fileContent);

        $servicesLineNumber = array_search('services:', $yamlLines, true) + 1;
        if ($servicesLineNumber === false) {
            $servicesLineNumber = count($yamlLines);
            $yamlLines[] = 'services:';
        }
        $endOfServicesLineNumber = $servicesLineNumber + 1;
        for ($i = $endOfServicesLineNumber; $i < count($yamlLines); $i++) {
            $line = $yamlLines[$i];

            if (mb_strpos(trim($line), $serviceClass . ':') === 0) {
                throw new RuntimeException('Service ' . $serviceClass . ' is already registered in services.yaml, skipping.');
            }

            if (mb_strpos($yamlLines[$i], '    ') === 0 || empty(trim($yamlLines[$i]))) { // check if the line is indented with 2 spaces or is empty, which means we're still in the services section
                $endOfServicesLineNumber = $i;
            } else { // we're no longer in the services section due to indentation
                break;
            }
        }

        $serviceConfig = [];
        $serviceConfig[$serviceClass] = [
            'class' => $serviceClass,
        ];

        if (!empty($arguments)) {
            $serviceConfig[$serviceClass]['arguments'] = $arguments;
        }

        $serviceConfigYaml = yaml_emit($serviceConfig, YAML_UTF8_ENCODING, YAML_LN_BREAK);
        $serviceConfigYaml = trim($serviceConfigYaml);
        $addedLinesYaml = explode(PHP_EOL, $serviceConfigYaml);
        // remove the first line which is "---" and the last line which is "..."
        array_shift($addedLinesYaml);
        array_pop($addedLinesYaml);
        // add an empty line before and after the added lines for better readability
        array_unshift($addedLinesYaml, '');
        array_push($addedLinesYaml, '');
        $serviceConfigYaml = implode(PHP_EOL, $addedLinesYaml);
        // change to default indentation instead of 2
        $serviceConfigYaml = str_replace('  ', YamlModifier::INDENTATION, $serviceConfigYaml);
        $serviceConfigYaml = YamlModifier::INDENTATION . str_replace(PHP_EOL, PHP_EOL . YamlModifier::INDENTATION, $serviceConfigYaml);

        // Insert the new service config at the correct position
        array_splice($yamlLines, $endOfServicesLineNumber + 1, 0, $serviceConfigYaml);

        $yamlString = implode(PHP_EOL, $yamlLines);

        return $yamlString;
    }

    private static function checkIfBundleClassIsValid(string $bundleClassName): void
    {
        if (!class_exists($bundleClassName)) {
            throw new RuntimeException('Bundle class ' . $bundleClassName . ' does not exist');
        }

        $reflectionClass = new ReflectionClass($bundleClassName);
        //check if it implements the BundleInterface
        if (!$reflectionClass->implementsInterface(BundleInterface::class)) {
            throw new RuntimeException('Bundle class ' . $bundleClassName . ' does not implement the Symfony BundleInterface. This will not be added to bundles.php');
        }
    }

}
