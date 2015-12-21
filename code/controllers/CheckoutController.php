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

/*
            if ($this->_getQuote()->getIsMultiShipping()) {
                $this->_getQuote()->setIsMultiShipping(false);
                $this->_getQuote()->removeAllAddresses();
            }

            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $quoteCheckoutMethod = $this->_getQuote()->getCheckoutMethod();
            if ($customer && $customer->getId()) {
                $this->_checkout->setCustomerWithAddressChange(
                    $customer, $this->_getQuote()->getBillingAddress(), $this->_getQuote()->getShippingAddress()
                );
            } elseif ((!$quoteCheckoutMethod
                || $quoteCheckoutMethod != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
                && !Mage::helper('checkout')->isAllowedGuestCheckout(
                $this->_getQuote(),
                $this->_getQuote()->getStoreId()
            )) {
                Mage::getSingleton('core/session')->addNotice(
                    Mage::helper('paypal')->__('To proceed to Checkout, please log in using your email address.')
                );
                $this->redirectLogin();
                Mage::getSingleton('customer/session')
                    ->setBeforeAuthUrl(Mage::getUrl('', array('_current' => true)));
                return;
            }
*/
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
//            $this->_checkout->prepareOrderReview($this->_initToken());

            $this->loadLayout();
//            $this->_initLayoutMessages('paypal/session');
//            $reviewBlock = $this->getLayout()->getBlock('paypal.express.review');
//            $reviewBlock->setQuote($this->_getQuote());
//            $reviewBlock->getChild('details')->setQuote($this->_getQuote());
//            if ($reviewBlock->getChild('shipping_method')) {
//                $reviewBlock->getChild('shipping_method')->setQuote($this->_getQuote());
//            }
            $this->renderLayout();
            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Unable to initialize Express Checkout review.')
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
