<?php
/**
 * Custom renderer for easyCredit-Ratenkauf - Click and collect
 */
class Netzkollektiv_EasyCredit_Block_Adminhtml_System_Config_ClickAndCollectIntro extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $_template = 'easycredit/system/config/clickandcollect_intro.phtml';

    /**
     * Set template to itself
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate($this->_template);
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return '<td class="label" colspan="3">' . $this->_getElementHtml($element) . '</td>';
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
}
