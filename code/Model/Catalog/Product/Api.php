<?php

class RMO_Integrator_Model_Catalog_Product_Api extends Mage_Catalog_Model_Product_Api {
   
    
   public function listCreated($curPage = null, $pageSize = null) {
        $productIntegratorCollection = $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::CREATED, 
                $curPage, $pageSize);
        $products = $this->_loadProductsByProductIntegrator($productIntegratorCollection);
        
        $result = array();
        foreach ($products as $product) {
            $result[] = $this->_toArray($product);
        }

        return $result;
    }
    
    /**
     * 
     * 
     * @param type $product
     * @return type
     */
    protected function _toArray($product) {
        $attributes[] = array( 'key' => 'product_id', 'value' => $product->getId());
        $attributes[] = array( 'key' => 'sku', 'value' => $product->getSku());
        $attributes[] = array( 'key' => 'set', 'value'        => $product->getAttributeSetId());
        $attributes[] = array( 'key' => 'type', 'value'       => $product->getTypeId());
        $attributes[] = array( 'key' => 'categories', 'value' =>  $this->implodeIfArray($product->getCategoryIds()));
        $attributes[] = array( 'key' => 'websites', 'value'   => $this->implodeIfArray($product->getWebsiteIds()));
        
        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute)) {
                $attributes[] = Array ("key" => $attribute->getAttributeCode(), "value" => $this->implodeIfArray($product->getData($attribute->getAttributeCode())));
                
                Mage::log($attribute->debug());
            }
        }
        
        $result['attributes'] = $attributes;
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $result['configurable_attributes'] = Mage::helper("rmointegrator")->getConfigurableAttributes($product);
            $result["variations"] = Mage::helper("rmointegrator")->getConfigurableProductVariation($product);
        }
        
        return  $result;
    }
    
    public function implodeIfArray($data) {
        if (is_array($data)) {
           return implode(' ; ', $data);
        } else {
            return $data;
        }
    }
    
    public function listCreatedCount() {
        return $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::CREATED)->count();
    }
    
    public function confirmListCreatedReceived($receivedSkus) {
        $this->_deleteProductIntegratorsBySku($receivedSkus, RMO_Integrator_Model_Status::CREATED);
        return true;
    }
    
    public function listUpdated($curPage = null, $pageSize = null) {
        $productIntegratorCollection = $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::UPDATED, 
                $curPage, $pageSize);
        $products = $this->_loadProductsByProductIntegrator($productIntegratorCollection);
        
        $result = array();
        foreach ($products as $product) {
            $result[] = $this->_toArray($product);
        }

        return $result;
    }
    
    public function listUpdatedCount() {
        return $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::UPDATED)->count();
    }
    
    public function confirmListUpdatedReceived($receivedSkus) {
        $this->_deleteProductIntegratorsBySku($receivedSkus, RMO_Integrator_Model_Status::UPDATED);
        return true;
    }
    
    public function listDeleted($curPage = null , $pageSize = null) {
        $collection = $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::DELETED, 
                $curPage, $pageSize);
        $skusToReturn = array();
        foreach ($collection as $product_integrator) {
            if ($product_integrator->getProductSku()) {
                $skusToReturn[]= $product_integrator->getProductSku();
            }
        }
        return $skusToReturn;
    }
    
    public function listDeletedCount() {
        return $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::DELETED)->count();
    }
    
    public function confirmListDeletedReceived($receivedSkus) {
        $this->_deleteProductIntegratorsBySku($receivedSkus, RMO_Integrator_Model_Status::DELETED);
        return true;
    }
    
    protected function _getProductIntegratorCollection($status, $curPage = null, $pageSize = null) {
        $collection = Mage::getModel("rmointegrator/catalog_product_integrator")->getCollection()
                       ->addFieldToFilter('status', array('eq' => $status));
        if ($curPage && $pageSize) {
            $collection->setPageSize($pageSize)->setCurPage($curPage);
        }
        
        return $collection;
    }
    
    protected function _loadProductsByProductIntegrator($productIntegratorCollection) {
        $productsToReturn = array();
        foreach ($productIntegratorCollection as $product_integrator) {
           $product = Mage::getModel("catalog/product")->loadByAttribute("sku", $product_integrator->getProductSku());
           if ( $product && $product->getId() && !$this->_shouldSkipProduct($product) ) {
               $productsToReturn[]= $product;
           }
        }
        return $productsToReturn;
    }
    
    protected function _shouldSkipProduct($product) {
        return Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE == $product->getVisibility();
    }
   
    protected function _deleteProductIntegratorsBySku($arrayOfSkus, $productIntegratorStatus) {
        foreach($arrayOfSkus as $sku) {
            $product_integrator = Mage::getModel("rmointegrator/catalog_product_integrator")->loadByProductSku($sku);
            if ($product_integrator->getId() && $product_integrator->getStatus() == $productIntegratorStatus) {
                $product_integrator->delete();
            }
        }
    }
}
