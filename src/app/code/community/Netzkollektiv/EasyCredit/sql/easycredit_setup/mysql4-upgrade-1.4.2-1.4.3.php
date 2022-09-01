<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->query("UPDATE {$this->getTable('core_config_data')} Set value = REPLACE(value, 'ratenkauf by easyCredit','easyCredit-Ratenkauf') WHERE path = 'payment/easycredit/title';");
$installer->endSetup();
