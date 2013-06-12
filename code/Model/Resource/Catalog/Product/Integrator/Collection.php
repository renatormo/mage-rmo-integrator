<?php

class RMO_Integrator_Model_Resource_Catalog_Product_Integrator_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract { 
    
    protected function _construct() {
            $this->_init('rmointegrator/catalog_product_integrator');
    }
}