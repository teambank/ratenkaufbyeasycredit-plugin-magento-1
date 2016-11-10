<?php
class Netzkollektiv_EasyCredit_Model_Checkout extends Varien_Object {

    /**
     * @return string
     */
    public function getRedirectUrl() {
        $token = $this->_getToken();
        return 'https://ratenkauf.easycredit.de/ratenkauf/content/intern/einstieg.jsf?vorgangskennung='.$token;
    }

    /**
     * @returns void
     */
    public function start() {
        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $result = $this->getApi()
            ->callProcessInit(
                $quote,
                $this->getCancelUrl(),
                $this->getReturnUrl(),
                $this->getRejectUrl()
            );

        $this->getPayment()->setAdditionalInformation(
            'token',
            $result->tbVorgangskennung
        )->setAdditionalInformation(
            'transaction_id',
            $result->fachlicheVorgangskennung
        )->setAdditionalInformation(
            'authorized_amount',
            $quote->getGrandTotal()
        );

        $quote->collectTotals()->save();
    }

    /**
     * @return bool
     */
    public function isInitialized() {
        try {
            $this->_getToken();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return array|mixed|null
     * @throws Exception
     */
    protected function _getToken() {
        $token = $this->getPayment()
            ->getAdditionalInformation('token');

        if (empty($token)) {
            throw new Exception('EasyCredit payment not initialized');
        }
        return $token;
    }

    /**
     * @return Mage_Sales_Model_Quote_Payment
     */
    public function getPayment() {
        return Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getPayment();
    }

    /**
     * @return bool
     */
    public function isApproved() {
        $token = $this->_getToken();
        $result = $this->getApi()->callDecision($token);

        if (!isset($result->entscheidung->entscheidungsergebnis)
            || $result->entscheidung->entscheidungsergebnis != 'GRUEN'
        ) {
            return false;
        }
        return true;
    }

    public function loadFinancingInformation() {
        $token = $this->_getToken();

        $payment = $this->getPayment();

        /* get transaction status from api */
        $result = $this->getApi()->callStatus($token);
        
        $payment->setAdditionalInformation(
            'pre_contract_information_url',
            (string)$result->allgemeineVorgangsdaten->urlVorvertraglicheInformationen
        );
        $payment->setAdditionalInformation(
            'redemption_plan',
            (string)$result->tilgungsplanText
        );

        /* get financing info from api */
        $result = $this->getApi()->callFinancing($token);

        $payment->setAdditionalInformation(
            'interest_amount',
            (float)$result->ratenplan->zinsen->anfallendeZinsen
        );
    }

    /**
     * @param null|string $token
     * @return mixed|string
     */
    public function capture($token = null) {
        if (is_null($token)) {
            $token = $this->_getToken();
        }

        return $this->getApi()
            ->callConfirm($token);
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Netzkollektiv_EasyCredit_Model_Api
     */
    public function getApi() {
        return Mage::getSingleton('easycredit/api');
    }
}
