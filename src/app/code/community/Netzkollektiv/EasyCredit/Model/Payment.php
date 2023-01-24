<?php
use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

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

    protected $_canOrder = true;

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
        return trim((string)$this->getConfigData('instructions'));
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     */
    public function validate()
    {
        $data = Mage::app()->getRequest()->getParam('easycredit');
        if (isset($data['number-of-installments'])) {
            $this->getInfoInstance()->setAdditionalInformation('duration', $data['number-of-installments']);
        }

        parent::validate();
        return $this;
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
                $ecQuote = new \Netzkollektiv\EasyCredit\Api\QuoteBuilder();
                Mage::helper('easycredit')->getCheckout()->isAvailable(
                    $ecQuote->build()
                );
            } catch (Exception $e) {
                $active = false;
            }
        }
        return $active;
    }

    /**
     * Order easyCredit payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     *
     * @return $this
     */
    public function order(Varien_Object $payment, $amount)
    {
        if (!$this->canOrder()) {
            Mage::throwException(Mage::helper('payment')->__('Order action is not available.'));
        }

        try {
            if (!Mage::helper('easycredit')->getCheckout()->authorize($payment->getOrder()->getIncrementId())) {
                Mage::throwException(Mage::helper('payment')->__('Transaction could not be authorized'));
            }

            $payment->setTransactionId(
                $payment->getAdditionalInformation('transaction_id')
            )->setIsTransactionClosed(false)
                ->setIsTransactionPending(true);
        } catch (\Exception $e) {
            Mage::throwException(Mage::helper('payment')->__($e->getMessage()));
        }
        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     *
     * @return $this
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        try {
            $txId = $payment->getAdditionalInformation('transaction_id');

            $this->_getTransaction($txId);

            Mage::helper('easycredit')
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdCapturePost(
                    $txId,
                    new CaptureRequest([])
                );

            $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, 'easyCredit Transaction captured');

        } catch (Exception $e) {
            Mage::throwException(Mage::helper('payment')->__($e->getMessage()));
        }
        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        try {
            $txId = $payment->getAdditionalInformation('transaction_id');

            $this->_getTransaction($txId);

            Mage::helper('easycredit')
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdRefundPost(
                    $txId,
                    new RefundRequest(['value' => $amount])
                );
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('payment')->__($e->getMessage()));
        }
        return $this;
    }

    protected function _getTransaction($txId)
    {
        try {
            $transaction = Mage::helper('easycredit')
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdGet($txId);
        } catch (ApiException $e) {
            throw new \Exception(
                'Payment transaction not found.
                It can take up to 24 hours until the transaction is available in the merchant portal.
                If you still want to create the invoice immediately, please use "Capture Offline".'
            );
        } catch (\Exception $e) {
            throw new \Exception(
                'An error occured when searching the transaction.'
            );
        }
        return $transaction;
    }
}
