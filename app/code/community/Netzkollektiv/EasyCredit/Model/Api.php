<?php

class Netzkollektiv_EasyCredit_Model_Api extends Varien_Object
{

    const API_BASE_URL = 'https://www.easycredit.de/ratenkauf-ws/rest';
    const API_VERSION = 'v0.3';

    const API_SHOP_ID_PLACEHOLDER = '%shopId%';
    const API_VERIFY_CREDENTIALS = 'webshop/%shopId%/restbetragankaufobergrenze';
    const API_VERIFY_CREDENTIALS_METHOD = 'GET';

    const API_TEXT_CONSENT = 'texte/zustimmung';

    protected $_apiBaseUrl = self::API_BASE_URL;
    protected $_apiVersion = self::API_VERSION;

    protected $_customerPrefixMalePatterns = array('Herr', 'Mr', 'male', 'männlich');
    protected $_customerPrefixFemalePatterns = array('Frau', 'Ms', 'Miss', 'Mrs', 'female', 'weiblich');

    /**
     * @param string $method
     * @param null|mixed $postData
     * @return resource
     */
    protected function _getRequestContext($method, $postData = null)
    {

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json, text/plain, */*',
            'tbk-rk-shop' => $this->_getWebshopId(),
            'tbk-rk-token' => $this->getToken(),
        );

        $ctx = array('http' => array(
            'ignore_errors' => true
        ));

        if (!is_null($postData)) {
            $headers['Content-Type'] = 'application/json;charset=UTF-8';
            $ctx['http']['content'] = json_encode($postData);
        }

        foreach ($headers as $key => $header) {
            $headers[$key] = implode(': ', array($key, $header));
        }

        $ctx['http']['method'] = strtoupper($method);
        $ctx['http']['header'] = implode("\r\n", $headers);

        return stream_context_create($ctx);
    }

    /**
     * @return string
     */
    protected function _getWebshopId()
    {
        return Mage::getStoreConfig('payment/easycredit/api_key');
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return Mage::getStoreConfig('payment/easycredit/api_token');
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function callProcessInit($quote, $cancelUrl, $returnUrl, $rejectUrl)
    {
        $data = $this->getProcessInitRequest($quote, $cancelUrl, $returnUrl, $rejectUrl);

        return $this->call('POST', 'vorgang', $data);
    }

    /**
     * @param mixed $amount
     * @return mixed|string
     */
    public function callModelCalculation($amount)
    {
        $data = array(
            'webshopId' => $this->_getWebshopId(),
            'finanzierungsbetrag' => $amount,
        );

        return $this->call('GET', 'modellrechnung/durchfuehren', $data);
    }

    /**
     * @param string $token
     * @return mixed|string
     */
    public function callDecision($token)
    {
        return $this->call('GET', 'vorgang/' . $token . '/entscheidung');
    }

    /**
     * @param string $token
     * @return mixed|string
     */
    public function callStatus($token)
    {
        return $this->call('GET', 'vorgang/' . $token);
    }

    /**
     * @param string $token
     * @return mixed|string
     */
    public function callFinancing($token)
    {
        return $this->call('GET', 'vorgang/' . $token . '/finanzierung');
    }

    /**
     * @param string $token
     * @return mixed|string
     */
    public function callConfirm($token)
    {
        return $this->call('POST', 'vorgang/' . $token . '/bestaetigen');
    }

    protected function _buildUrl($method, $resource)
    {
        $url = implode('/', array(
            $this->_apiBaseUrl,
            $this->_apiVersion,
            $resource
        ));
        return $url;
    }

    protected function _log($data)
    {
        $store = Mage::app()->getStore()->getStoreId();
        $debug = Mage::getStoreConfig('payment/easycredit/debug_logging', $store);
        if ($debug) {
            Mage::log($data, null, 'easycredit.log');
        }
    }

    /**
     * @param $method
     * @param $resource
     * @param array $data
     * @param null $webShopId
     * @param null $webShopToken
     * @return mixed|string
     * @throws Mage_Core_Exception
     */
    public function call($method, $resource, $data = array(), $webShopId = null, $webShopToken = null)
    {

        if ($webShopId === null) {
            $webShopId = $this->_getWebshopId();
        }
        if ($webShopToken === null) {
            $webShopToken = $this->getToken();
        }

        $url = $this->_buildUrl($method, $resource);
        $method = strtoupper($method);

        $this->_log($data);

        $client = new Zend_Http_Client($url, array(
            'keepalive' => true
        ));
        $client->setHeaders(array(
            'Accept' => 'application/json, text/plain, */*',
            'tbk-rk-shop' => $webShopId,
            'tbk-rk-token' => $webShopToken
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

        if ($response->isError()) {
            $e = array(
                "method" => $method,
                "resource" => $resource,
                "data" => $data,
                "url" => $url,
                "responseCode" => $response->getStatus()
            );

            $this->_log($e);
            Mage::throwException('connection problem');
        }

        $result = $response->getBody();

        if (empty($result)) {
            Mage::throwException('result is empty');
        }
        $result = json_decode($result);
        $this->_log($result);

        if ($result == null) {
            Mage::throwException('result is null');
        }

        if (isset($result->wsMessages)) {
            $this->_handleMessages($result);
        }
        return $result;
    }

    /**
     * @param mixed $result
     * @throws Mage_Core_Exception
     * @return void
     */
    protected function _handleMessages($result)
    {
        if (!isset($result->wsMessages->messages)) {
            unset($result->wsMessages);
            return;
        }

        foreach ($result->wsMessages->messages as $message) {
            switch (trim($message->severity)) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'ERROR':
                    Mage::throwException($message->renderedMessage);
                case 'INFO':
                    $this->_log($message->renderedMessage);
                    break;
            }
        }
        unset($result->wsMessages);
    }

    /**
     * @param $date
     * @return false|null|string
     */
    protected function _getFormattedDate($date)
    {
        return (strtotime($date) !== false) ? date('Y-m-d', strtotime($date)) : null;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @param bool $isShipping
     * @return array
     */
    protected function _convertAddress(Mage_Sales_Model_Quote_Address $address, $isShipping = false)
    {
        $_address = array(
            'strasseHausNr' => $address->getStreet(1),
            'adresszusatz' => is_array($address->getStreet()) ? implode(',', array_slice($address->getStreet(), 1)) : '',
            'plz' => $address->getPostcode(),
            'ort' => $address->getCity(),
            'land' => $address->getCountryId()
        );

        if ($isShipping && stripos(implode(" ", $address->getStreet()), 'packstation')) {
            $_address['packstation'] = true;
        }

        return $_address;
    }


    /**
     * @param string $prefix
     * @return string
     */
    protected function _guessCustomerPrefix($prefix)
    {
        foreach ($this->_customerPrefixMalePatterns as $pattern) {
            if (stripos($prefix, $pattern) !== false) {
                return 'HERR';
            }
        }
        foreach ($this->_customerPrefixFemalePatterns as $pattern) {
            if (stripos($prefix, $pattern) !== false) {
                return 'FRAU';
            }
        }
        return '';
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    protected function _convertPersonalData(Mage_Sales_Model_Quote $quote)
    {

        $prefix = $this->_guessCustomerPrefix($quote->getCustomerPrefix());

        return array(
            'anrede' => $prefix,
            'vorname' => $quote->getCustomerFirstname(),
            'nachname' => $quote->getCustomerLastname(),
            'geburtsdatum' => $this->_getFormattedDate($quote->getCustomerDob())
        );
    }

    protected function _getDeepestCategoryName($categoryIds)
    {
        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $categoryId = end($categoryIds);
            return Mage::getResourceModel('catalog/category')->getAttributeRawValue(
                $categoryId,
                'name',
                Mage::app()->getStore()->getId()
            );
        }

        return null;
    }

    /**
     * @param array $items
     * @return array
     */
    public function _convertItems(array $items)
    {
        $_items = array();

        foreach ($items as $item) {
            $_item = array(
                'produktbezeichnung' => $item->getName(),
                'menge' => $item->getQty(),
                'preis' => $item->getPrice(),
                'hersteller' => $item->getProduct()->getManufacturer(),
            );

            $_item['produktkategorie'] = $this->_getDeepestCategoryName(
                $item->getProduct()->getCategoryIds()
            );
            $_item['artikelnummern'][] = array(
                'nummerntyp' => 'magento-sku',
                'nummer' => $item->getSku()
            );

            $_items[] = array_filter($_item);
        }
        return $_items;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    protected function _getCustomerOrderCount($customer) {
        return Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id',$customer->getId())
            ->count();
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    protected function _convertRiskDetails($quote) {
        /**
         * @var Mage_Customer_Model_Session $session
         */
        $session = Mage::getSingleton('customer/session');

        $details = array(
            'bestellungErfolgtUeberLogin'   => $session->isLoggedIn(),
            'anzahlProdukteImWarenkorb'     => count($quote->getAllVisibleItems())
        );
        if ($session->isLoggedIn()) {
            $customer = $session->getCustomer();

            $details = array_merge($details, array(
                'kundeSeit'                     => date('Y-m-d', $customer->getCreatedAtTimestamp()),
                'anzahlBestellungen'            => $this->_getCustomerOrderCount($customer),
            ));
        }
        return $details;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param string $cancelUrl
     * @param string $returnUrl
     * @param string $rejectUrl
     * @return array
     */
    public function getProcessInitRequest($quote, $cancelUrl, $returnUrl, $rejectUrl)
    {
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
            'risikorelevanteAngaben' => $this->_convertRiskDetails($quote),
            'rechnungsadresse' => $this->_convertAddress($quote->getBillingAddress()),
            'lieferadresse' => $this->_convertAddress($quote->getShippingAddress(), true),
            'warenkorbinfos' => $this->_convertItems($quote->getAllVisibleItems()),
        ));
    }

    /**
     * @param string $apiKey
     * @param string $apiToken
     * @return bool
     */
    public function verifyCredentials($apiKey, $apiToken)
    {
        $resource = str_replace(self::API_SHOP_ID_PLACEHOLDER, $apiKey, self::API_VERIFY_CREDENTIALS);

        try {
            $this->call(self::API_VERIFY_CREDENTIALS_METHOD, $resource, [], $apiKey, $apiToken);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getTextConsent()
    {

        if (empty($this->_getWebshopId())) {
            $this->_log("easyCredit apiKey not configured, but extension activated.");
            Mage::throwException("easyCredit apiKey not configured, but extension activated.");
        }

        $resource = self::API_TEXT_CONSENT . '/' . $this->_getWebshopId();

        $result = $this->call('GET', $resource);

        return $result->zustimmungDatenuebertragungServiceIntegration;
    }
}
