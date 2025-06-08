<?php

declare(strict_types=1);

namespace DocPHT\core\Translator;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PhpFileLoader;

class TranslationLoader
{
    private Translator $translator;

    public function __construct(string $locale)
    {
        $this->translator = new Translator($locale);
        $this->translator->addLoader('php', new PhpFileLoader());

        // Define the path to your translations directory
        $translationsDir = __DIR__ . '/../../translations/';

        // Add a resource for the specified locale
        if (file_exists($translationsDir . $locale . '.php')) {
            $this->translator->addResource('php', $translationsDir . $locale . '.php', $locale);
        }
        
        // Add a fallback locale
        $fallbackLocale = 'en_EN';
        if ($locale !== $fallbackLocale && file_exists($translationsDir . $fallbackLocale . '.php')) {
             $this->translator->addResource('php', $translationsDir . $fallbackLocale . '.php', $fallbackLocale);
             $this->translator->setFallbackLocales([$fallbackLocale]);
        }
    }

    public function getTranslator(): Translator
    {
        return $this->translator;
    }
}