<?php
class Orba_Lekkikoszyk_Model_Attribute extends Mage_Core_Model_Abstract {
    
    protected function _construct() {
        $this->_init('lekkikoszyk/attribute');
    } 
    
    protected function getConfig() {
        return Mage::getModel('lekkikoszyk/config');
    }
    
    public function toOptionArray() {
        $entity_type_id = $this->getEntityTypeId();
        if ($entity_type_id) {
            $attributes_collection = Mage::getModel('catalog/entity_attribute')->getCollection()
                    ->addFieldToFilter('entity_type_id', $entity_type_id);
            $res = array();
            foreach ($attributes_collection as $attribute) {
                $res[$attribute->getAttributeCode()] = array(
                    'label' => $attribute->getAttributeCode(),
                    'value' => $attribute->getAttributeCode()
                );
            }
            ksort($res);
        }
        return $res;
    }
    
    protected function getEntityTypeId() {
        $collection = Mage::getModel('eav/entity_type')->getCollection()
                ->addFieldToFilter('entity_type_code', 'catalog_product');
        $item = $collection->getFirstItem();
        return $item->getId();
    }
    
    public function addLekkikoszykAttributeToProduct() {
        $this->createAttribute('add_to_lekkikoszyk', 'Add to LekkiKoszyk.pl', 'select', null, array(
            'backend_type' => 'int',
            'source_model' => 'eav/entity_attribute_source_boolean',
            'sort_order' => 210,
            'is_global' => 1,
        ), false, 'General');
        $product_collection = Mage::getModel('catalog/product')->getCollection();
        foreach ($product_collection as $product) {
            Mage::getSingleton('lekkikoszyk/product')->saveAttribute($product, 'add_to_lekkikoszyk', '1');
        }
    }
    
    public function createAttribute($code, $label, $attribute_type, $product_type, $attribute_data, $attribute_set_name, $group_name, $entity_type_id = 'catalog_product') {
        $default_attribute_data = array(
            'attribute_code' => $code,
            'is_global' => 0,
            'frontend_input' => $attribute_type, 
            'frontend_class' => '',
            'default_value_text' => '',
            'default_value_yesno' => 0,
            'default_value_date' => '',
            'default_value_textarea' => '',
            'is_unique' => 0,
            'is_required' => 0,
            'apply_to' => array($product_type),
            'is_configurable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_used_for_price_rules' => 0,
            'is_wysiwyg_enabled' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'frontend_label' => array($label),
            'sort_order' => 0,
            'is_user_defined' => 0
        );
        $attribute_data = array_merge($default_attribute_data, $attribute_data);
        $model = Mage::getModel('catalog/resource_eav_attribute');
        if (!isset($attribute_data['backend_type'])) {
            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $attribute_data['backend_type'] = $model->getBackendTypeByInput($attribute_data['frontend_input']);
            }
        }
        $model->addData($attribute_data);
        $model->setEntityTypeId(Mage::getModel('eav/entity')->setType($entity_type_id)->getTypeId());
        try {
            $model->save();
            if (isset($attribute_data['source_model'])) {
                $model->setSourceModel($attribute_data['source_model'])->save();
            }
            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
            $attribute_code = $code;
            $attribute_set_ids = array();
            if ($attribute_set_name) {
                $attribute_set_ids[] = $setup->getAttributeSetId($entity_type_id, $attribute_set_name);
            } else {
                $attribute_set_ids = $setup->getAllAttributeSetIds($entity_type_id);
            }
            $attribute_id = $setup->getAttributeId($entity_type_id, $attribute_code);
            foreach ($attribute_set_ids as $attribute_set_id) {
                $attribute_group_id = $setup->getAttributeGroupId($entity_type_id, $attribute_set_id, $group_name);
                $setup->addAttributeToSet($entity_type_id, $attribute_set_id, $attribute_group_id, $attribute_id, $attribute_data['sort_order']);
            }
        } catch (Exception $e) {
            Mage::log("Couldn't create attribute $code. Exception: ".$e->getMessage(), null, 'lekkikoszyk.log');
        }
    } 
    
}