<?php

namespace OC\PlatformBundle\Antispam;

class OCAntispam
{
    private $mailer;
    private $locale;
    private $minLentgh;

    public function __construct(\Swift_Mailer $mailer, $minLentgh){
        $this->mailer = $mailer;
        $this->minLentgh = (int) $minLentgh;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function isSpam($text){
        return strlen($text) < $this->minLentgh;
    }
}