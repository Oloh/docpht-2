<?php

declare(strict_types=1);

namespace DocPHT\Core\Translator;

use Symfony\Contracts\Translation\TranslatorInterface;

class T
{
    private static ?TranslatorInterface $translator = null;

    /**
     * Initializes the translator so it can be used anywhere.
     */
    public static function init(TranslatorInterface $translator): void
    {
        self::$translator = $translator;
    }

    /**
     * Translates the given text using the initialized translator.
     */
    public static function trans(string $text): string
    {
        if (self::$translator === null) {
            return $text;
        }
        return self::$translator->trans($text);
    }
}