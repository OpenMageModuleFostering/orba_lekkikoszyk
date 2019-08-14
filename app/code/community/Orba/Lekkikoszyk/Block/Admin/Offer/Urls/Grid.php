<?php
class Orba_Lekkikoszyk_Block_Admin_Offer_Urls_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    
    public function __construct() {
        parent::__construct();
        $this->setId('lekkikoszyk_offer_urls_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
    }
    
    protected function getConfig() {
        return Mage::getModel('lekkikoszyk/config');
    }
    
    protected function _prepareCollection(){
        $collection = Mage::getModel('core/store')->getCollection();
        foreach ($collection as &$item) {
            $item->setLekkikoszykUrl($item->getUrl('lekkikoszyk/products/feed', array('hash' => $this->getConfig()->getHash())));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        $this->addColumn('store_id', array(
            'header' => Mage::helper('lekkikoszyk')->__('Store ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'store_id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('lekkikoszyk')->__('Store Name'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'name',
        ));
        $this->addColumn('lekkikoszyk_url', array(
            'header' => Mage::helper('catalog')->__('LekkiKoszyk.pl Feed URL'),
            'align' => 'left',
            'index' => 'lekkikoszyk_url',
        ));
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row) {
        return $row->getLekkikoszykUrl();
    }
    
}