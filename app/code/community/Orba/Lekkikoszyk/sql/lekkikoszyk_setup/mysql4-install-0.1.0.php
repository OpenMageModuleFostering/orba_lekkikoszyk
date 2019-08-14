<?php
$installer = $this;
$installer->startSetup();

Mage::getModel('lekkikoszyk/attribute')->addLekkikoszykAttributeToProduct();
Mage::getModel('lekkikoszyk/config')->saveHash();

@mail('magento@orba.pl', '[Instalacja] Lekkikoszyk.pl 0.1.0', "IP: ".$_SERVER['SERVER_ADDR']."\r\nHost: ".gethostbyaddr($_SERVER['SERVER_ADDR']));

$installer->endSetup();