<?php declare(strict_types=1);

namespace Tbessenreither\Copycat\Modifier;


class JsonModifier
{

    public static function add(string $fileContent, string $path, mixed $value): string
    {
        $jsonData = json_decode($fileContent, true);

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

        $fileContentModified = json_encode($jsonData, JSON_PRETTY_PRINT);

        return $fileContentModified;
    }

}
