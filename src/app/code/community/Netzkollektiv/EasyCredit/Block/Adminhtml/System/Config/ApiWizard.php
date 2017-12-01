<?php
/**
 * Custom renderer for EasyCredit API credentials wizard
 */
class Netzkollektiv_EasyCredit_Block_Adminhtml_System_Config_ApiWizard extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $_wizardTemplate = 'easycredit/system/config/api_wizard.phtml';

    /**
     * Set template to itself
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate($this->_wizardTemplate);
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
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        $elementHtmlId = $element->getHtmlId();
        $this->addData(array_merge(
            $this->_getButtonData($elementHtmlId, $originalData)
        ));
        return $this->_toHtml();
    }

    /**
     * Prepare button data
     *
     * @param string $elementHtmlId
     * @param array $originalData
     * @return array
     */
    protected function _getButtonData($elementHtmlId, $originalData)
    {
        return array(
            'input_incomplete_message' => Mage::helper('easycredit')->__('Please insert your apiKey and apiToken!'),
            'success_message' => Mage::helper('easycredit')->__('Credentials correct!'),
            'error_message' => Mage::helper('easycredit')->__('Could not contact easyCredit, please try again later.'),
            'button_label' => Mage::helper('easycredit')->__($originalData['button_label']),
            'button_url'   => $originalData['button_url'],
            'html_id' => $elementHtmlId,
        );
    }
}
