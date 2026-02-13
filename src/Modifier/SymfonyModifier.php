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
            echo "Bundle $bundleClassName is already registered in bundles.php, skipping." . PHP_EOL;

            return $fileContent;
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
        echo "Inserting bundle $bundleClassName at line $insertIndex in bundles.php." . PHP_EOL;
        array_splice($lines, $insertIndex, 0, $bundleLine);

        return implode(PHP_EOL, $lines);
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
