<?php

$this->startSetup();

@mail('magento@orba.pl', '[Upgrade] LekkiKoszyk.pl 0.1.2', "IP: ".$_SERVER['SERVER_ADDR']."\r\nHost: ".gethostbyaddr($_SERVER['SERVER_ADDR']));

$this->endSetup();