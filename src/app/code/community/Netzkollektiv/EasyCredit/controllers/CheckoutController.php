<?php
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

            $checkout = Mage::helper('easycredit')->getCheckout();
            $checkout->start(
                new \Netzkollektiv\EasyCredit\Api\Quote(),
                Mage::getUrl('*/*/cancel'),
                Mage::getUrl('*/*/return'),
                Mage::getUrl('*/*/reject')
            );

            $this->_getQuote()->collectTotals()->save();

            if ($url = $checkout->getRedirectUrl()) {
                $this->getResponse()->setRedirect($url);
                return;
            }
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

            $checkout = Mage::helper('easycredit')->getCheckout();

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

    /**
     * JSON controller, returns consent text for shop
     */
/*    public function consentAction() {
        $checkout = Mage::getSingleton('easycredit/checkout');

        $status = false;
        $errorMessage = "";
        $text = '';



        try {
            $text = $easyCreditApi->getTextConsent();
            $status = true;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $result = [
            'status' => $status,
            'text' => $text,
            'errorMessage' => $errorMessage
        ];

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
*/
}
