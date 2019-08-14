<?php
class Orba_Lekkikoszyk_Model_Config extends Mage_Core_Model_Abstract {
    
    public static $attrs = array('Producent', 'Kod_producenta', 'EAN');
    
    public function _construct() {
        $this->_init('lekkikoszyk/config');
    }
    
    public function getAttrs() {
        $attrs = Mage::getStoreConfig('lekkikoszyk/config/attrs', $this->getStore());
        if (!$attrs) {
            return array();
        }
        return explode(',', $attrs);
    }
    
    public function getPriceIncludesTax() {
        return Mage::getStoreConfig('tax/calculation/price_includes_tax');
    }
    
    public function getStore() {
        return Mage::app()->getStore();
    }
    
    public function saveHash() {
        $hash = md5(microtime());
        Mage::getModel('core/config')->saveConfig('lekkikoszyk/config/hash', $hash, 'default', 0);
    }
    
    public function getHash() {
        return Mage::getStoreConfig('lekkikoszyk/config/hash');
    }
    
}