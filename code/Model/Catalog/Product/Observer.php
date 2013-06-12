<?php

class RMO_Integrator_Model_Catalog_Product_Observer {
    
    public function catalog_product_save_before($observer) {
        $product = $observer->getProduct();
        // Tried to use the Mage_Core_Model_Abstract's method: isObjectNew but
        // for some reason when duplicating a product in the admin gui, it was always
        // returning false in the catalog_product_save_after observer. 
        // So we needed this hack bellow.
        if (!$product->getId()) {
            $product->setIsNewProduct(true);
        } 
    }
    
    /**
     *
     * This function only considers products that returns true when  the following function
     * returns true.
     * 
     * Mage::getModel("rmointegrator/catalog_product_filter")->isProductTypeValid($product)
     * 
     * 
     * Creates a record in the product_integrator table. The status of the new entry
     * depends on the save action type (wether a new product has been created or edited ) AND
     * on the current status of the related product_integrator entry (if it exits). To understand
     * the status change, the reader can use the following table:
     * 
     *      ACTION	CURRENT STATUS          NEW STATUS
     *        edit         created	           new
     *        new	     n/a                   new
     *        new	   deleted               updated
     *        edit           n/a                 updated
     *        edit         updated               updated
     *
     * n/a -> Means that the product currently does not have any entry in the product_integrator table
     * 
     * The remaing combinations (Action, current status) that are not present in the table above
     * were not included because they describe situations impossible to happen.
     *  
     * @see catalog_product_delete_after
     * @param type $observer
     * @return nothing
     */
    public function catalog_product_save_after($observer) {       
        $product = $observer->getProduct();
        if (!Mage::getModel("rmointegrator/catalog_product_filter")->isProductTypeValid($product)) {
            return;
        }
        $integratorStatus = null; 
        $integratorProduct = Mage::getModel("rmointegrator/catalog_product_integrator")->loadByProductData($product);
        if ( ($product->getIsNewProduct() && !$integratorProduct->getId()) ||
             (!$product->getIsNewProduct() && $integratorProduct->isStatusCreated()) ) {
            $integratorStatus = RMO_Integrator_Model_Status::CREATED;
        } else {
            $integratorStatus = RMO_Integrator_Model_Status::UPDATED;
        }
        $integratorProduct->setProductData($product, $integratorStatus);
        $integratorProduct->save();
    }
    
    /**
     * This function only considers products that returns true when  the following function
     * returns true.
     * 
     * Mage::getModel("rmointegrator/catalog_product_filter")->isProductTypeValid($product)
     * 
     * Always when a product is deleted, this function should be executed. It will
     * either create/update the entry in the product_integrator table that is related
     * to the deleted product OR it will remove this releted table. 
     * 
     * The existing entry in the product_integrator table will be removed if its status is set
     * to "CREATED". It will create a new entry (or updated a existing one) and set its status 
     * to "DELETED", otherwise.
     * 
     * @param type $observer
     * @return type
     */
    public function catalog_product_delete_after($observer) {
        $product = $observer->getProduct();
        if (!Mage::getModel("rmointegrator/catalog_product_filter")->isProductTypeValid($product)) {
            return;
        }
        $integratorProduct = Mage::getModel("rmointegrator/catalog_product_integrator")->loadByProductData($product);
        if ($integratorProduct->isStatusCreated()) {
            $integratorProduct->delete();
        } else {
            $integratorProduct->setProductData($product, RMO_Integrator_Model_Status::DELETED);
            $integratorProduct->save();
        }
    }
}