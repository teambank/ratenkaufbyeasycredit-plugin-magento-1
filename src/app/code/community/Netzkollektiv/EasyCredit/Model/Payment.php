<?php

class Netzkollektiv_EasyCredit_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    CONST CODE = 'easycredit';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code  = self::CODE;

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'easycredit/form';
    protected $_infoBlockType = 'easycredit/info';

    protected $_canOrder                    = false;
    protected $_canAuthorize                = false;

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

    public function isAvailable($quote = null) {
        $active = parent::isAvailable($quote);
        if ($active && !$this->getConfigData('active_when_unavailable')) {
            try {
                Mage::helper('easycredit')->getCheckout()->isAvailable(
                    new \Netzkollektiv\EasyCredit\Api\Quote()
                );
            } catch (Exception $e) {
                $active = false;
            }
        }
        return $active;
    }

    /**
     * Capture payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Netzkollektiv_EasyCredit_Model_Payment
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        Mage::helper('easycredit')->getCheckout()
            ->capture();

        $this->getInfoInstance()->setAdditionalInformation(
            'is_captured', 1
        );

        return $this;
    }
}
