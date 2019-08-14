<?php
class Orba_Lekkikoszyk_ProductsController extends Mage_Core_Controller_Front_Action {
	
    protected function getConfig() {
        return Mage::getModel('lekkikoszyk/config');
    }
    
    public function feedAction() {
        $hash = $this->getRequest()->getParam('hash');
        if ($hash == $this->getConfig()->getHash()) {
            ini_set('max_execution_time', 0);
            header("Content-Type:text/xml");
            require_once(Mage::getBaseDir('lib').'/Lekkikoszyk/simple_xml_extended.php');
            $items = Mage::getModel('lekkikoszyk/product')->getOffers();
            $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><items />');
            foreach ($items as $product) {
                $item = $xml->addChild('item');
                $item->addChild('id', $product['id']);
                $item->addChild('name')
                        ->addCData($product['name']);
                $item->addChild('price', $product['price']);
                $item->addChild('url')
                        ->addCData($product['url']);
                $item->addChild('category')
                        ->addCData($product['category']);
                $item->addChild('description')
                        ->addCData($product['description']);
                if (!empty($product['imgs'])) {
                    $item->addChild('image')
                        ->addCData($product['imgs'][0]);
                }
                $item->addChild('quantity', $product['quantity']);
                $item->addChild('weight', $product['weight']);
                if (!empty($product['attrs']) || (!empty($product['imgs']) && isset($product['imgs'][1]))) {
                    $attrs = $item->addChild('attrs');
                }
                if (!empty($product['attrs'])) {
                    foreach ($product['attrs'] as $code => $value) {
                        $attr = $attrs->addChild('attr', $value);
                        $attr->addAttribute('name', $code);
                    }
                }
                if (!empty($product['imgs']) && isset($product['imgs'][1])) {
                    foreach ($product['imgs'] as $key => $value) {
                        if ($key > 0) {
                            $attr = $attrs->addChild('attr', $value);
                            $attr->addAttribute('name', 'img'.$key);
                        }
                    }
                }
            }
            echo $xml->asXML();
            die();
        } else {
            $this->_redirect('/');
        }
    }
    
}