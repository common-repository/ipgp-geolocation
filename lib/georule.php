<?php

class Georule
{
    public $activecountries;
	 public $redirecturl;    
    
    public function __construct($activecountries,$redirecturl)
    {
        $this->activecountries = $activecountries;
		  $this->redirecturl = $redirecturl;
    }
}

