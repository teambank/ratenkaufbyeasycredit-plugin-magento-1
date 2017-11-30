<?php
$installer = $this;
$installer->startSetup();

/* Remove obsolete product attribute 'easycredit_risk' */
$setup = Mage::getResourceModel('catalog/setup','catalog_setup');
$setup->removeAttribute('catalog_product','easycredit_risk');

/* Remove obsolete customer attribute 'easycredit_risk' */
$setup = Mage::getResourceModel('customer/setup','customer_setup');
$setup->removeAttribute('customer','easycredit_risk');

$installer->endSetup();
