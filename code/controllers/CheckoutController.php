<?php
class Netzkollektiv_Easycredit_CheckoutController extends Mage_Core_Controller_Front_Action {

    protected function _validateQuote() {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('easycredit')->__('Unable to initialize easyCredit Payment.'));
        }
    }

    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected $_quote = null;

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

            $checkout = Mage::getSingleton('easycredit/checkout');
            $checkout->setReturnUrl(Mage::getUrl('*/*/return'))
                ->setCancelUrl(Mage::getUrl('*/*/cancel'))
                ->setRejectUrl(Mage::getUrl('*/*/reject'));
            $checkout->start();

            if ($url = $checkout->getRedirectUrl()) {
                $this->getResponse()->setRedirect($url);
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Unable to start easyCredit Payment.'));
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }

    public function returnAction() {
        try {
            $this->_validateQuote();

            $checkout = Mage::getSingleton('easycredit/checkout');
            if (!$checkout->isApproved()) {
                throw new Exception('transaction not approved'); 
            }
            $checkout->loadFinancingInformation();

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
        try {
            $this->_validateQuote();

            $checkout = Mage::getSingleton('easycredit/checkout');
            if (!$checkout->isInitialized()) {
                throw new Exception('payment not initialized');
            }

            $this->loadLayout();
            $this->renderLayout();
            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Unable to initialize Easycredit Checkout review.')
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
}
