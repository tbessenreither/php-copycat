<?php declare(strict_types=1);

namespace Tbessenreither\PhpCopycat\Modifier;

use RuntimeException;


class JsonModifier
{

    public static function add(string $file, string $path, mixed $value): void
    {
        $jsonData = json_decode(file_get_contents($file), true);
        if ($jsonData === null) {
            throw new RuntimeException('Failed to decode JSON file: ' . $file);
        }

        $keys = explode('.', $path);
        $current = &$jsonData;
        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = null;
            }
            $current = &$current[$key];
        }

        if (is_array($current)) {
            if (!is_array($value)) {
                $value = [$value];
            }
            foreach ($value as $key => $item) {
                if (is_string($key)) {
                    $current[$key] = $item;
                } elseif (!in_array($item, $current)) {
                    $current[] = $item;
                }
            }
        } else {
            $current = $value;
        }

        file_put_contents($file, json_encode($jsonData, JSON_PRETTY_PRINT));
    }

}
