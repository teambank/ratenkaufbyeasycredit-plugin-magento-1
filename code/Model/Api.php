<?php
class Netzkollektiv_EasyCredit_Model_Api extends Varien_Object {

    protected $_apiBaseUrl = 'https://www.easycredit.de/ratenkauf-ws/rest';
    protected $_apiVersion = 'v0';

    protected $_customerPrefixMalePatterns = array('Herr','Mr','male','männlich');
    protected $_customerPrefixFemalePatterns = array('Frau','Ms','Miss','Mrs','female','weiblich');

    protected function _getRequestContext($method, $postData = null) {

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json, text/plain, */*',
            'tbk-rk-shop' => $this->_getWebshopId(),
            'tbk-rk-token' => $this->getToken(),
        ); 

        $ctx = array('http'=>array(
            'ignore_errors' => true
        ));

        if (!is_null($postData)) {
            $headers['Content-Type'] = 'application/json;charset=UTF-8';
            $ctx['http']['content'] = json_encode($postData);
        }

        foreach ($headers as $key => $header) {
            $headers[$key] = implode(': ',array($key,$header));
        }

        $ctx['http']['method'] = strtoupper($method);
        $ctx['http']['header'] = implode("\r\n",$headers);

        return stream_context_create($ctx);
    }

    protected function _getWebshopId() {
        return Mage::getStoreConfig('payment/easycredit/api_key');
    }

    public function getToken() {
        return Mage::getStoreConfig('payment/easycredit/api_token');
    }

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    protected function _mergeBaseData($data) {
        return $data;
    }

    public function callProcessInit($quote, $cancelUrl, $returnUrl, $rejectUrl) {
        $data = $this->getProcessRequest($quote, $cancelUrl, $returnUrl, $rejectUrl);

        return $this->call('POST','vorgang', $data);
    }

    public function callModelCalculation($amount) {
        $data = array(
            'webshopId' => $this->_getWebshopId(),
            'finanzierungsbetrag' => $amount,
        );

        return $this->call('GET','modellrechnung/durchfuehren', $data);
    }

    public function callDecision($token) {
        return $this->call('GET','vorgang/'.$token.'/entscheidung');
    }

    public function callStatus($token) {
        return $this->call('GET','vorgang/'.$token);
    }

    public function callFinancing($token) {
        return $this->call('GET','vorgang/'.$token.'/finanzierung');
    }

    public function callConfirm($token) {
        return $this->call('POST','vorgang/'.$token.'/bestaetigen');
    }

    protected function _buildUrl($method, $resource) {
        $url = implode('/',array(
            $this->_apiBaseUrl,
            $this->_apiVersion,
            $resource
        ));
Mage::log($url);
        return $url;
    }

    public function call($method, $resource, $data = array()) { 

        $url = $this->_buildUrl($method, $resource);
        $method = strtoupper($method);
        $data = $this->_mergeBaseData($data);

        $client = new Zend_Http_Client($url,array(
            'keepalive' => true
        ));
        $client->setHeaders(array(
            'Accept' => 'application/json, text/plain, */*',
            'tbk-rk-shop' => $this->_getWebshopId(),
            'tbk-rk-token' => $this->getToken()
        ));

        if ($method == 'POST') { 
            $client->setRawData(
                json_encode($data), 
                'application/json;charset=UTF-8'
            );
            $data = null;
        } else {
            $client->setParameterGet($data);
        }

        $response = $client->request($method);

Mage::log($client->getLastRequest());
Mage::log($client->getLastResponse());

        if ($response->isError()) {
            throw new Exception(print_r($response,true));
        }

        $result = $response->getBody();

        if (empty($result)) {
            throw new Exception('result is empty');
        }
        $result = json_decode($result);
Mage::log($result);

        if ($result == null) {
            throw new Exception('result is null');
        }

        if (isset($result->wsMessages)) {
            $this->_handleMessages($result);
        }
        Mage::log($result);
        return $result;
    }

    protected function _handleMessages($result) {
        if (!isset($result->wsMessages->messages)) {
            unset($result->wsMessages);
            return;
        }
        $messages = $result->wsMessages->messages;

        foreach ($result->wsMessages->messages as $message) {
            switch (trim($message->severity)) {
                case 'ERROR':
                    Mage::throwException($message->renderedMessage);
                case 'INFO':
                    Mage::log($message->renderedMessage);
                    break;
            }
        }
        unset($result->wsMessages);
    }

    protected function _convertAddress(Mage_Sales_Model_Quote_Address $address) {
        return array(
             'strasseHausNr' => $address->getStreet(1),
             'adresszusatz' => is_array($address->getStreet()) ? implode(',',array_slice($address->getStreet(),1)) : '',
             'plz' => $address->getPostcode(),
             'ort' => $address->getCity(),
             'land' => $address->getCountryId()
        );
    }


    protected function _guessCustomerPrefix($prefix) {
        foreach ($this->_customerPrefixMalePatterns as $pattern) {
            if (stripos($prefix,$pattern) !== false) {
                return 'HERR';
            }
        }
        foreach ($this->_customerPrefixFemalePatterns as $pattern) {
            if (stripos($prefix,$pattern) !== false) {
                return 'FRAU';
            }
        }
    }

    protected function _convertPersonalData($quote) {

        // Workaround: Anrede nicht vorhanden
        $prefix = $this->_guessCustomerPrefix($quote->getCustomerPrefix());
        if (null == $prefix) {
            return array();
        }

        return array(
            'anrede' => $prefix,
            'vorname' => $quote->getCustomerFirstname(),
            'nachname' => $quote->getCustomerLastname(),
            'geburtsdatum' => $quote->getCustomerDob(),
        );
    }

    public function getProcessRequest($quote, $cancelUrl, $returnUrl, $rejectUrl) {
        return array_filter(array(
           'shopKennung' => $this->_getWebshopId(),
           'bestellwert' => $quote->getGrandTotal(),
           'ruecksprungadressen' => array(
               'urlAbbruch' => $cancelUrl,
               'urlErfolg' => $returnUrl,
               'urlAblehnung' => $rejectUrl
           ),
           'laufzeit' => 36,
           'personendaten' => $this->_convertPersonalData($quote),
           'kontakt' => array(
             'email' => $quote->getCustomerEmail(),
           ),
           'rechnungsadresse' => $this->_convertAddress($quote->getBillingAddress()),
           'lieferAdresse' => $this->_convertAddress($quote->getShippingAddress()),
        ));
    }
}
