<?php
class Netzkollektiv_EasyCredit_Model_Customer_Attribute_Risk extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    public function getAllOptions()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('easycredit')->__('keine Auswahl')),
            array('value' => 0, 'label' => Mage::helper('easycredit')->__('keine Information')),
            array('value' => 1, 'label' => Mage::helper('easycredit')->__('keine Zahlungsstörungen')),
            array('value' => 2, 'label' => Mage::helper('easycredit')->__('Zahlungsverzögerung')),
            array('value' => 3, 'label' => Mage::helper('easycredit')->__('Zahlungsausfall')),
        );
    }
}
