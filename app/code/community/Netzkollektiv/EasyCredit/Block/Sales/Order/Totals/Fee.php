<?php
class Netzkollektiv_Easycredit_Block_Sales_Order_Totals_Fee extends Mage_Core_Block_Template {

    public function initTotals() {

        $field = 'easycredit_amount';
        if((float) $this->getParentBlock()->getSource()->getData($field) == 0) {
            return $this;
        }

        $total = new Varien_Object(array(
            'code'  => $this->getNameInLayout(),
            'value' => $this->getParentBlock()->getSource()->getData($field),
            'label' => Mage::helper('easycredit')->__('Interest')
        ));
        $after = 'subtotal';

        $this->getParentBlock()->addTotal($total, $after);
        return $this;
    }
}
