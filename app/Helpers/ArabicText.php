<?php

namespace App\Helpers;

class ArabicText
{
    protected static $arabic;

    public static function reshape($text)
    {
        if (empty($text)) return '';

        // New library (khaled.alshamaa/ar-php)
        if (class_exists('ArPHP\I18N\Arabic')) {
            if (!self::$arabic) {
                self::$arabic = new \ArPHP\I18N\Arabic();
            }
            return self::$arabic->utf8Glyphs($text);
        }

        return $text;
    }
}
