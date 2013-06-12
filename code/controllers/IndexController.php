<?php
class RMO_Integrator_IndexController extends Mage_Core_Controller_Front_Action  {
    
    public function indexAction() {
        echo "starting...";
        $integratorProduct = Mage::getModel('rmointegrator/catalog_product_integrator');
        $integratorProduct->importCurrentProducts();
        echo "done";
    }
}

