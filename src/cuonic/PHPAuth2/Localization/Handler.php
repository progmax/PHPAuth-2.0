<?php
namespace cuonic\PHPAuth2\Localization;

require_once 'en.php';
require_once 'es.php';
require_once 'fr.php';

class Handler
{
    private $locale;

    public function __construct($data, $locale = 'en')
    {
        $locale = '\cuonic\PHPAuth2\Localization\\'.$locale;
        if (class_exists($locale)) {
            $this->locale = new $locale($data);
        } else {
            throw new \Exception('Language template does not exist.');
        }
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getActivationEmail()
    {
        throw new \Exception('This function is not implemented.');
    }

    public function getResetEmail()
    {
        throw new \Exception('This function is not implemented.');
    }
}
