<?php
$installer = $this;
$installer->startSetup();

// keep interest behavior for upgrades
$entries = $installer->getConnection()->fetchOne("SELECT COUNT(config_id) FROM {$this->getTable('core_config_data')} WHERE path LIKE 'payment/easycredit/%';");
if ($entries > 0) {
  $setup = new Mage_Core_Model_Config();
  $setup->saveConfig('payment/easycredit/remove_interest', '0', 'default', 0);
}
$installer->endSetup();
