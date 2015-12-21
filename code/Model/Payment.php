<?php
class Netzkollektiv_EasyCredit_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code  = 'easycredit';

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'easycredit/form';
    protected $_infoBlockType = 'easycredit/info';

//    protected $_canOrder                    = false;
//    protected $_canAuthorize                = false;
    protected $_canCapture                  = true;

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see Mage_Checkout_OnepageController::savePaymentAction()
     * @see Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return Mage::getUrl('easycredit/checkout/start');
    }

    /**
     * Order payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function order(Varien_Object $payment, $amount)
    {
        if (!$this->canOrder()) {
            Mage::throwException(Mage::helper('payment')->__('Order action is not available.'));
        }
        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
/*     public function authorize(Varien_Object $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
        }
        return $this;
    }
*/
    /**
     * Capture payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        $token = $this->getInfoInstance()->getAdditionalInformation('token');

        $this->_getCheckout()
            ->capture($token);

        return $this;
    }

    protected function _getCheckout() {
        return Mage::getSingleton('easycredit/checkout');
    }

/*
    public function getPreContractInformationUrl() {
Mage::log($this->getAdditionalInformation());
        return $this->getAdditionalInformation('pre_contract_information_url');
    }

    public function getRedemptionPlan() {
        return $this->getAdditionalInformation('redemption_plan');
    }

    public function getInterestAmount() {
        return $this->getAdditionalInformation('interest_amount');
    }
*/
}
