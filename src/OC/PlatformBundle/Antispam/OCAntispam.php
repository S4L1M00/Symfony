<?php

namespace OC\PlatformBundle\Antispam;

class OCAntispam
{
    private $mailer;
    private $locale;
    private $minLentgh;

    public function __construct(\Swift_Mailer $mailer, $locale, $minLentgh){
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->minLentgh = (int) $minLentgh;
     }

    public function isSpam($text){
        return strlen($text) < $this->minLentgh;
    }
}