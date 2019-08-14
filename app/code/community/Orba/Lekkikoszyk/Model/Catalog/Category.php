<?php
class Orba_Lekkikoszyk_Model_Catalog_Category extends Mage_Catalog_Model_Category {
    
    protected $name_paths = array();
    protected $path_length = array();
    
    public function getNamePathOfDeepest($category_ids) {
        $max_length = 0;
        $deepest_id = false;
        foreach ($category_ids as $id) {
            if (!isset($this->path_length[$id])) {
                $category = Mage::getModel('catalog/category')->load($id);
                $path = $category->getPath();
                $path_array = explode('/', $path);
                $this->path_length[$id] = count($path_array);
            }
            if ($this->path_length[$id] > $max_length) {
                $max_length = $this->path_length[$id];
                $deepest_id = $id;
            }
        }
        return $this->getNamePath($deepest_id);
    }
    
    public function getNamePath($id) {
        if (!isset($this->name_paths[$id])) {
            $category = Mage::getModel('catalog/category')->load($id);
            $parent_id = $category->getParentId();
            $path = explode('/', $category->getPath());
            if ($parent_id && count($path) > 3) {
                $this->name_paths[$id] = $this->getNamePath($parent_id).'/'.$category->getName();
            } else {
                $this->name_paths[$id] = $category->getName();
            }
        }
        return $this->name_paths[$id];
    }
    
}