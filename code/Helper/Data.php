<?php

class RMO_Integrator_Helper_Data extends Mage_Core_Helper_Abstract {
    
    
    /**
     * Returns all attributes that make up the $configurableProduct
     * 
     * It returns a bi-dimensional array of the form:
     *   $result = { { "attribute_name" => "...", attribute_code" => "..." }, 
     *               { "attribute_name" => "...", attribute_code" => "..." }, 
     *               ...} 
     * 
     * If the procuts passsed as argument is not a configurable product, the function will return the boolean false
     * 
     * @param type $configurableProduct
     * @return boolean
     */
    public function getConfigurableAttributes($configurableProduct) {
        if (!$configurableProduct || $configurableProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return false;
        }
        $configurableAttributes = $configurableProduct->getTypeInstance()->getConfigurableAttributes();
        $result = array();
        foreach($configurableAttributes as $attribute) {
            $resultItem['attribute_name'] = $attribute->getProductAttribute()->getFrontend()->getLabel();
            $resultItem['attribute_code'] = $attribute->getProductAttribute()->getAttributeCode();
            $result[] = $resultItem;
        }
        
        return $result;
    }
    
    /**
     * Returns the ids of the chield products of the configurable product
     * 
     * If the product passed as argument is not a configurable product, the funciton returns the boolean false.
     * 
     * @param type $configurableProduct
     * @return boolean
     */
    public function getConfigurableAssociatedProducts($configurableProduct) {
        if (!$configurableProduct || $configurableProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return false;
        }
        
        $array = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configurableProduct->getId());
        return array_values($array[0]); 
        
    }
    
    public function getConfigurableProductVariation($configurableProduct) {
        if (!$this->isConfigurableProduct($configurableProduct)) {
            return false;
        }
        $array = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configurableProduct->getId());
        $associatedIds = array_values($array[0]); 
        $configurableAttributes = $this->getConfigurableAttributes($configurableProduct);
        $result = Array();   
        foreach ($associatedIds as $productId) {
            $variation = Array();
            $product = Mage::getModel("catalog/product")->load($productId);
            $variation['associated_product_id'] = $productId;
            $variation_attributes = Array();
            foreach ($configurableAttributes as $attributeData) {
                $variation_attributes[] = Array("key" => $attributeData["attribute_code"], "value" => $this->implodeIfArray($product->getAttributeText($attributeData['attribute_code'])));
            }
            $variation["attributes"] = $variation_attributes;
            $result[] = $variation;
        }

        return $result;
    }
    
    
    protected function isConfigurableProduct($configurableProduct) {
        return $configurableProduct && $configurableProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
    }
    
   public function implodeIfArray($data) {
        if (is_array($data)) {
           return implode(' ; ', $data);
        } else {
            return $data;
        }
    }
}
