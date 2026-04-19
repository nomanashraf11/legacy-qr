<?php

namespace App\Support;

class TabVisibility
{
    /**
     * Which main nav sections can be hidden (Legacy stays always available).
     */
    public const DEFAULTS = [
        'family_tree' => true,
        'gallery' => true,
        'timeline' => true,
        'tribute' => true,
    ];

    /**
     * @param  array<string, mixed>|null  $stored
     * @return array<string, bool>
     */
    public static function merge(?array $stored): array
    {
        $base = self::DEFAULTS;
        if (! is_array($stored)) {
            return $base;
        }
        foreach (array_keys($base) as $key) {
            if (array_key_exists($key, $stored)) {
                $base[$key] = (bool) $stored[$key];
            }
        }

        return $base;
    }
}
