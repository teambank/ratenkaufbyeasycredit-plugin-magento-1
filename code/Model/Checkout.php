<?php
class Netzkollektiv_Easycredit_Model_Checkout extends Varien_Object {

    public function getRedirectUrl() {
        $token = $this->_getToken();
        return 'https://ratenkauf.easycredit.de/ratenkauf/content/intern/einstieg.jsf?vorgangskennung='.$token;
    }

    public function start() {
        $quote = Mage::getSingleton('checkout/session')
            ->getQuote();

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
            'authorized_amount',
            $quote->getGrandTotal()
        );

        $quote->collectTotals()->save();
    }

    public function isInitialized() {
        try {
            $this->_getToken();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _getToken() {
        $token = $this->getPayment()
            ->getAdditionalInformation('token');

        if (empty($token)) {
            throw new Exception('EasyCredit payment not initialized');
        }
        return $token;
    }

    public function getPayment() {
        return Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getPayment();
    }

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

    public function capture($token = null) {
        if (is_null($token)) {
            $token = $this->_getToken();
        }

        return $this->getApi()
            ->callConfirm($token);
    }

    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function getApi() {
        return Mage::getSingleton('easycredit/api');
    }
}
