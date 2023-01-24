<?php
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

use Netzkollektiv\EasyCredit\Api as Api;

class Netzkollektiv_EasyCredit_CheckoutController extends Mage_Core_Controller_Front_Action {

    protected function _validateQuote() {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('easycredit')->__('Unable to initialize easyCredit Payment.'));
        }
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected $_quote = null;

    /**
     * @return Mage_Sales_Model_Quote|null
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    public function startAction()
    {
        try {

            $this->_validateQuote();

            $ecQuote = new \Netzkollektiv\EasyCredit\Api\QuoteBuilder();
            $checkout = Mage::helper('easycredit')->getCheckout();
            $checkout->start(
                $ecQuote->build()
            );

            $this->_getQuote()->collectTotals()->save();

            if ($url = $checkout->getRedirectUrl()) {
                $this->getResponse()->setRedirect($url);
                return;
            }
        } catch (ApiException $e) {
            $response = json_decode((string) $e->getResponseBody());
            if ($response === null || !isset($response->violations)) {
                throw new \Exception('violations could not be parsed');
            }
            $messages = [];
            foreach ($response->violations as $violation) {
                $messages[] = $violation->field . ': ' . $violation->message;
            }

            $logger = new Api\Logger();
            $logger->notice('easyCredit-Ratenkauf could not be initalized: '.implode(', ',$messages));
            $this->_getCheckoutSession()->addError($this->__('Unable to initialize easyCredit Payment.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($this->__($e->getMessage()));
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }

    public function returnAction() {
        try {
            $this->_validateQuote();

            Mage::helper('easycredit')
                ->getCheckout()
                ->loadTransaction();

            $quote = $this->_getQuote();
            $quote->getPayment()
                ->setMethod('easycredit');
            $quote->collectTotals()->save();
        
            $this->_redirect('*/*/review');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Unable to validate easyCredit Payment.'));
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    public function reviewAction() {
        /**
         * @var Mage_Checkout_Model_Session $checkoutSession
         */
        $checkoutSession = Mage::getSingleton('checkout/session');

        try {
            $this->_validateQuote();

            $checkout = Mage::helper('easycredit')->getCheckout();

            if (!$checkout->isInitialized()) {
                throw new Exception('payment not initialized');
            }

            $this->loadLayout();
            $this->renderLayout();
            return;
        }
        catch (Mage_Core_Exception $e) {
            $checkoutSession->addError($e->getMessage());
        }
        catch (Exception $e) {
            $checkoutSession->addError(
                $this->__('Unable to initialize easyCredit Checkout review.')
            );
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    public function cancelAction() {

        $this->_getCheckoutSession()
            ->addSuccess($this->__('easyCredit payment has been canceled.'));
        $this->_redirect('checkout/cart');

    }

    public function rejectAction() {

        $this->_getCheckoutSession()
            ->addSuccess($this->__('Unfortunately, easyCredit payment cannot be offered.'));
        $this->_redirect('checkout/cart');
    }

    public function authorizeAction()
    {
        $secToken = $this->getRequest()->getParam('secToken');
        $txId = $this->getRequest()->getParam('transactionId');
        $incrementId = $this->getRequest()->getParam('orderId');

        if (!$txId) {
            throw new \Exception('no transaction ID provided');
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        if ($order->getState() != Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            throw new \Exception('order status not valid for authorization');
        }

        $payment = $order->getPayment();
        if (!isset($payment->getAdditionalInformation()['sec_token'])
            && $secToken !== $payment->getAdditionalInformation()['sec_token']
        ) {
            throw new \Exception('secToken not valid');
        }

        $token = $payment->getAdditionalInformation()['token'] ?? null;
        $tx =  Mage::helper('easycredit')
            ->getCheckout()
            ->loadTransaction($token);

        if ($tx->getStatus() !== TransactionInformation::STATUS_AUTHORIZED) {
            throw new \Exception('payment status of transaction not updated as transaction status is not AUTHORIZED');
        }

        $payment->setParentTransactionId($txId)
            ->setTransactionId($txId.'-authorize')
            ->setIsTransactionClosed(false)
            ->authorize(
                true,
                $payment->getBaseAmountOrdered()
            );

        $this->setNewOrderState($order);

        $order->save();
    }

    private function setNewOrderState($order)
    {
        if (! $order instanceof Mage_Sales_Model_Order) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod !== Netzkollektiv_EasyCredit_Model_Payment::CODE) {
            return;
        }

        $newOrderState = Mage::getStoreConfig('payment/easycredit/order_status', $order->getStoreId());

        if (empty($newOrderState)) {
            $newOrderState = Mage_Sales_Model_Order::STATE_PROCESSING;
        }
        $order->setState($newOrderState)
            ->setStatus($newOrderState);
    }

}
