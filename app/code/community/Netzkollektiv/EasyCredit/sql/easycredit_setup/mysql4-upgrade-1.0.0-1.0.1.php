<?php
$installer = $this;
$installer->startSetup();

/* Add customer attribute 'easycredit_risk' */

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$attributeCode    = 'easycredit_risk';

$installer->addAttribute('customer', $attributeCode,  array(
    'type'     => 'int',
    'label'    => 'Kundeneinstufung (für easyCredit)',
    'input'    => 'select',
    'source'   => 'easycredit/customer_attribute_risk',
    'visible'  => true,
    'required' => false,
    'unique'   => false,
    'note'     => 'Die Kundeneinstufung wird für die Zahlungsart easyCredit verwendet und hilft eine genauere Risikoeinstufung abzufragen.',
    'default'  => '',
    'position' => 20
));

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    $attributeCode,
    '35'
);

Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode)
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();

/* Add product attribute 'easycredit_risk' */

$attributeCode    = 'easycredit_risk';

$installer->addAttribute('catalog_product', $attributeCode, array(
    'label'             => 'risikorelevanter Artikel (für easyCredit)',
    'type'              => 'int',
    'source'            => 'eav/entity_attribute_source_boolean',
    'input'             => 'select',
    'visible'           => true,
    'required'          => false,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'             => 'General',
    'note'              => 'Die Artikeleinstufung wird für die Zahlungsart easycredit verwendet und hilft eine genauere Risikoentscheidung zu treffen.',
    'default'           => 0
));

Mage::getModel('eav/entity_attribute')
    ->loadByCode('catalog_product', $attributeCode)
    ->setStoreLabels(array())
    ->save();

$installer->endSetup();
