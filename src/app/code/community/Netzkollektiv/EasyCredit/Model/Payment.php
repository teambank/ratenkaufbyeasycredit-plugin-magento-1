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

    protected $_canOrder = false;

    protected $_canAuthorize = true;

    protected $_canCapture = true;

    protected $_canRefund = true;

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
     * Authorize easyCredit payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Netzkollektiv_EasyCredit_Model_Payment
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
        }
        
    
        Mage::helper('easycredit')->getCheckout()
            ->capture(null, $payment->getOrder()->getIncrementId());

        $payment->setTransactionId($payment->getAdditionalInformation('transaction_id'));
        $payment->setIsTransactionClosed(false);

        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        try {
            $txId = $payment->getAdditionalInformation('transaction_id');

            $this->_getTransaction($txId);

            Mage::helper('easycredit')->getMerchant()
                ->confirmShipment($txId);

            $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, 'easyCredit Transaction captured');

        } catch (Exception $e) {
            Mage::throwException(Mage::helper('payment')->__($e->getMessage()));
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        try {
            $txId = $payment->getAdditionalInformation('transaction_id');

            $this->_getTransaction($txId);

            Mage::helper('easycredit')->getMerchant()->cancelOrder(
                $txId,
                $payment->getAmountAuthorized() > $amount ? 'WIDERRUF_TEILWEISE' : 'WIDERRUF_VOLLSTAENDIG',
                new DateTime(),
                $amount
            );
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('payment')->__($e->getMessage()));
        }
        return $this;
    }

    protected function _getTransaction($txId)
    {
        $transaction = Mage::helper('easycredit')->getMerchant()
            ->getTransaction($txId);

        if (count($transaction) !== 1) {
            throw new Exception('Payment transaction not found. 
            It can take up to 24 hours until the transaction is available in the merchant portal. 
            If you still want to create the invoice immediately, please use "Capture Offline".');
        }
        return $transaction;
    }
}
