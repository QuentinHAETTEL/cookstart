<?php

namespace App\Core\Translator;

use App\Core\App;

class Translator
{
    private ?string $locale;


    public function __construct()
    {
        $this->locale = App::getInstance()->getConfig()->get('locale');
    }


    public function getLocale(): ?string
    {
        return $this->locale;
    }


    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }


    /**
     * @throws TranslatorException
     */
    public function getTranslations(): ?array
    {
        $file = '../translations/'.$this->locale.'.json';
        if (file_exists($file)) {
            $translations = file_get_contents($file);
            return json_decode($translations, true);
        } else {
            throw new TranslatorException('Translations file not found at '.$file);
        }
    }
}
