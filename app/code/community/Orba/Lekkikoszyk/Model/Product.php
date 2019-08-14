<?php
class Orba_Lekkikoszyk_Model_Product extends Mage_Catalog_Model_Product {
    
    protected function getConfig() {
        return Mage::getModel('lekkikoszyk/config');
    }
    
    public function getOffers() {
        $store = $this->getConfig()->getStore();
        $additional_attrs = $this->getConfig()->getAttrs();
        $_attribute = Mage::getModel('lekkikoszyk/attribute');
        $product_collection = $this->getCollection()
            ->addStoreFilter($store->getStoreId())
            ->addAttributeToSelect('category_ids')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('special_from_date')
            ->addAttributeToSelect('special_to_date')
            ->addAttributeToSelect('weight')    
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('tax_class_id')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('status')
            ->addAttributeToFilter('add_to_lekkikoszyk', 1);
        foreach ($additional_attrs as $code) {
            $product_collection->addAttributeToSelect($code);
        }
        $product_collection = $this->addMediaGalleryAttributeToCollection($product_collection);
        $offers = array();
        $_stock = Mage::getModel('cataloginventory/stock_item');
        $images_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product';
        foreach ($product_collection as $product) {
            if ($product->isVisibleInSiteVisibility() && $product->isVisibleInCatalog()) {
                $core_attrs = array();
                $_stock = $_stock->loadByProduct($product);
                $quantity = 0;
                if ($_stock->getManageStock()) {
                    $quantity = (int)$_stock->getQty();
                }
                $imgs = array();
                $media_gallery = $product->getMediaGallery();
                $images = (isset($media_gallery['images'])) ? $media_gallery['images'] : array();
                foreach ($images as $image) {
                    $imgs[] = $images_url.$image['file'];
                }
                $category_ids = $product->getCategoryIds();
                if (!empty($category_ids)) {
                    $category = Mage::getSingleton('lekkikoszyk/catalog_category')->getNamePathOfDeepest($category_ids);
                } else {
                    continue;
                }
                $price = $this->getFinalPriceIncludingTax($product);
                $attrs = array();
                foreach ($additional_attrs as $attr) {
                    $attrtibute = $product->getResource()->getAttribute($attr);
                    if ($attrtibute->usesSource()) {
                        $attrs[$attr] = $attrtibute->getSource()->getOptionText($product->getData($attr));
                    } else {
                        $attrs[$attr] = $product->getData($attr);
                    }
                    if (!$attrs[$attr]) {
                        unset($attrs[$attr]);
                    }
                }
                $offers[] = array(
                    'id' => $product->getEntityId(),
                    'url' => $product->getProductUrl(),
                    'price' => $price,
                    'name' => substr($product->getName(), 0, 64),
                    'description' => substr($product->getDescription() ? $product->getDescription() : $product->getShortDescription(), 0, 65536),
                    'weight' => (float)$product->getWeight(),
                    'quantity' => $quantity,
                    'imgs' => $imgs,
                    'category' => $category,
                    'attrs' => $attrs
                );
            }
        }
        return $offers;
    }
    
    public function addMediaGalleryAttributeToCollection($_productCollection) {
		$all_ids = $_productCollection->getAllIds();
		if (!empty($all_ids)) {
			$_mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
			$_read = Mage::getSingleton('core/resource')->getConnection('catalog_read');

			$_mediaGalleryData = $_read->fetchAll('
				SELECT
					main.entity_id, `main`.`value_id`, `main`.`value` AS `file`,
					`value`.`label`, `value`.`position`, `value`.`disabled`, `default_value`.`label` AS `label_default`,
					`default_value`.`position` AS `position_default`,
					`default_value`.`disabled` AS `disabled_default`
				FROM `'.Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery').'` AS `main`
					LEFT JOIN `'.Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value').'` AS `value`
						ON main.value_id=value.value_id AND value.store_id=' . Mage::app()->getStore()->getId() . '
					LEFT JOIN `'.Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value').'` AS `default_value`
						ON main.value_id=default_value.value_id AND default_value.store_id=0
				WHERE (
					main.attribute_id = ' . $_read->quote($_mediaGalleryAttributeId) . ') 
					AND (main.entity_id IN (' . $_read->quote($all_ids) . '))
				ORDER BY IF(value.position IS NULL, default_value.position, value.position) ASC    
			');


			$_mediaGalleryByProductId = array();
			foreach ($_mediaGalleryData as $_galleryImage) {
				$k = $_galleryImage['entity_id'];
				unset($_galleryImage['entity_id']);
				if (!isset($_mediaGalleryByProductId[$k])) {
					$_mediaGalleryByProductId[$k] = array();
				}
				$_mediaGalleryByProductId[$k][] = $_galleryImage;
			}
			unset($_mediaGalleryData);
			foreach ($_productCollection as &$_product) {
				$_productId = $_product->getData('entity_id');
				if (isset($_mediaGalleryByProductId[$_productId])) {
					$_product->setData('media_gallery', array('images' => $_mediaGalleryByProductId[$_productId]));
				}
			}
			unset($_mediaGalleryByProductId);
		}
        
        return $_productCollection;
    } 
    
    public function getFinalPriceIncludingTax($product) {
        return Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 2);
    }
    
    public function saveAttribute($product, $attribute_code, $value) {
        if ($product->getData($attribute_code) != $value) {
            $product->setData($attribute_code, $value);
            $product->getResource()->saveAttribute($product, $attribute_code);
            return true;
        }
        return false;
    }
    
}