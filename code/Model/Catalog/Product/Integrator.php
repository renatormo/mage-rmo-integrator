<?php

class RMO_Integrator_Model_Catalog_Product_Integrator extends Mage_Core_Model_Abstract {
    
    protected function _construct() {
        $this->_init('rmointegrator/catalog_product_integrator');
    }
    
    public function importCurrentProducts() {
        $products = Mage::getModel("rmointegrator/catalog_product_filter")->getProductCollection();
        foreach ($products as $product) {
            $integratorProduct = Mage::getModel("rmointegrator/catalog_product_integrator");
            $integratorProduct->setProductData($product, RMO_Integrator_Model_Status::CREATED);
            $integratorProduct->save();
        }
    }
    
    public function setProductData($product, $integrator_status) {
        $this->setProductId($product->getId());
        $this->setProductSku($product->getSku());
        $this->setStatus($integrator_status);
    }
    
    public function loadByProductId($product_id) {
        $this->load($product_id, "product_id");
        return $this;
    }
    
    public function loadByProductSku($product_sku) {
        $this->load($product_sku, "product_sku");
        return $this;
    }
    
    public function loadByProductData($product) {
        $this->loadByProductId($product->getId());
        if (!$this->getId()) {
            $this->loadByProductSku($product->getSku());
        }
        return $this;
    }
    
    public function isStatusCreated() {
        return $this->getStatus() == RMO_Integrator_Model_Status::CREATED;
    }
    
    public function isStatusUpdated() {
        return $this->getStatus() == RMO_Integrator_Model_Status::UPDATED;
    }
    
    public function isStatusDeleted() {
        return $this->getStatus() == RMO_Integrator_Model_Status::DELETED;
    }
 }