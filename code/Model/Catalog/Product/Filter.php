<?php

class RMO_Integrator_Model_Catalog_Product_Filter extends Mage_Core_Model_Abstract { 
    
    const CONFIGURABLE = 'configurable';
    
    const SIMPLE = 'simple';
    
    public function getProductCollection() {
       return Mage::getModel("catalog/product")->getCollection()
                       ->addAttributeToFilter('type_id',array('in' => array(self::CONFIGURABLE, self::SIMPLE )));
    }
    
    public function isProductTypeValid($product) {
        return $product->getTypeId() == self::CONFIGURABLE ||
               $product->getTypeId() == self::SIMPLE;
    }
    
}