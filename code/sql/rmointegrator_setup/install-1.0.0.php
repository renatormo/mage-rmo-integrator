<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `{$installer->getTable('rmointegrator/catalog_product_integrator')}` (
      `integrator_product_id` int(11) NOT NULL auto_increment,
      `product_id` int(11),
      `product_sku` text NOT NULL,
      `status` int(11) NOT NULL,
      PRIMARY KEY  (`integrator_product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;  ");
$installer->endSetup();