<?php


class MerchantInterfaceApi
{
    const LOG_ID = 'EasyCredit MerchantInterface';

    const API_BASE_URL = 'https://app.easycredit.de/haendlerinterface-ws/rest';
    const API_VERSION = 'v0';
    const API_BASE_PATH = 'haendlerinterface';

    const TRANSACTION_ID_PLACEHOLDER = '%transactionId%';

    const API_SEARCH_METHOD = 'GET';
    const API_SEARCH_RESOURCE = 'suchen';

    const API_SHIPMENT_METHOD = 'POST';
    const API_SHIPMENT_RESOURCE = '%transactionId%/lieferung';

    const API_CANCEL_METHOD = 'POST';
    const API_CANCEL_RESOURCE = '%transactionId%/rueckabwicklung';
    const API_CANCEL_REASON_COMPLETE = 'WIDERRUF_VOLLSTAENDIG';
    const API_CANCEL_REASON_PARTIAL = 'WIDERRUF_TEILWEISE';
    const API_CANCEL_REASON_RETURN = 'RUECKGABE_GARANTIE_GEWAEHRLEISTUNG';
    const API_CANCEL_REASON_IMPAIRMENT = 'MINDERUNG_GARANTIE_GEWAEHRLEISTUNG';

    /**
     * @var string
     */
    protected $webShopId;

    /**
     * @var string
     */
    protected $webShopToken;

    /**
     * @var string call_user_func target e.g. MyClass::Log
     */
    protected $loggingFunction;

    /**
     * @var string call_user_func target e.g. Mage::throwException
     */
    protected $exceptionFunction;

    private $cancelReasons = array(
        self::API_CANCEL_REASON_COMPLETE,
        self::API_CANCEL_REASON_PARTIAL,
        self::API_CANCEL_REASON_RETURN,
        self::API_CANCEL_REASON_IMPAIRMENT
    );

    /**
     * MerchantInterfaceApi constructor.
     * @param string $webShopId
     * @param string $webShopToken
     * @param string|null $loggingFunction
     * @param string|null $exceptionFunction
     */
    public function __construct($webShopId, $webShopToken, $loggingFunction=null, $exceptionFunction=null)
    {
        $this->webShopId = $webShopId;
        $this->webShopToken = $webShopToken;
        $this->loggingFunction = $loggingFunction;
        $this->exceptionFunction = $exceptionFunction;
    }

    /**
     * @param mixed $data
     */
    private function log($data) {
        if ($this->loggingFunction) {
            call_user_func($this->loggingFunction, $data);
        }
    }

    /**
     * @param string $errorMessage
     * @throws Exception
     */
    private function throwException($errorMessage) {
        if ($this->exceptionFunction) {
            call_user_func($this->exceptionFunction, $errorMessage);
        } else {
            throw new Exception($errorMessage);
        }
    }

    /**
     * @param array $parts
     * @return array
     */
    private function trimResourceParts(array $parts) {
        $clean = array();

        foreach ($parts as $p) {
            $clean[] = trim($p, " \t\n\r\0\x0B/");
        }

        return $clean;
    }

    /**
     * @param string $resource
     * @param string|null $transactionId
     * @return string
     * @throws Exception
     */
    private function getResourceUrl($resource, $transactionId = null) {
        if (
            strpos($resource, self::TRANSACTION_ID_PLACEHOLDER) !== false
            && $transactionId === null
        ) {
            $this->throwException("Resource requires a transactionId. No transactionId passed to getResourceUrl");
        } else {
            $resource = str_replace(self::TRANSACTION_ID_PLACEHOLDER, $transactionId, $resource);
        }

        $parts = array(
            self::API_BASE_URL,
            self::API_VERSION,
            self::API_BASE_PATH,
            $resource
        );

        $parts = $this->trimResourceParts($parts);

        return implode("/", $parts);
    }

    /**
     * @param mixed $result
     */
    private function handleAPIErrors($result) {
        if (!isset($result->wsMessages->messages)) {
            unset($result->wsMessages);
            return;
        }

        foreach ($result->wsMessages->messages as $message) {
            $this->log($message->renderedMessage);
            switch (trim($message->severity)) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case 'ERROR':
                    $this->throwException($message->renderedMessage);
                case 'INFO':
                    $this->log($message->renderedMessage);
                    break;
            }
        }
        unset($result->wsMessages);
    }

    /**
     * @param string $method
     * @param string $resource
     * @param string|null $transactionId
     * @param mixed|null $data
     * @return mixed|string
     */
    private function callApi($method, $resource, $transactionId=null, $data=null) {
        $method = strtoupper($method);
        $url = $this->getResourceUrl($resource, $transactionId);

        $client = new Zend_Http_Client($url, array('keepalive' => true));
        $client->setAuth($this->webShopId, $this->webShopToken, Zend_Http_Client::AUTH_BASIC);
        $client->setHeaders(array('Accept' => 'application/json, text/plain, */*',));

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
            $error = array(
                "error" => sprintf("%s Connection Error", self::LOG_ID),
                "method" => $method,
                "resource" => $resource,
                "data" => $data,
                "url" => $url,
                "responseCode" => $response->getStatus(),
                "responseError" => $response->getMessage()
            );

            $errorMessage = sprintf("%s Connection problem: %s '%s'", self::LOG_ID, $method, $resource);

            $this->log($error);

            $this->throwException($errorMessage);
        }

        $result = $response->getBody();
        if (empty($result)) {
            $errorMessage = sprintf("%s Body Empty: %s '%s'", self::LOG_ID, $method, $resource);

            $this->log($errorMessage);

            $this->throwException($errorMessage);
        }

        if (isset($result->wsMessages)) {
            $this->handleAPIErrors($result);
        }
        return $result;
    }

    /**
     * @param string|null $search
     * @param string|null $status
     * @param string|null $from
     * @param string|null $to
     * @return mixed|string
     */
    public function search($search=null, $status=null, $from=null, $to=null) {
        $data = array();

        if ($search) {
            $data['suche'] = $search;
        }

        if ($status) {
            $data['status'] = $status;
        }

        if ($from) {
            $data['von'] = $from;
        }

        if ($to) {
            $data['to'] = $to;
        }

        $results = $this->callApi(self::API_SEARCH_METHOD, self::API_SEARCH_RESOURCE, null, $data);

        return $results->ergebnisse;
    }

    /**
     * @param string $transactionId
     * @param string $shipmentDate
     * @return bool
     */
    public function confirmShipment($transactionId, $shipmentDate) {
        $data = array("datum" => $shipmentDate);

        $this->callApi(self::API_SHIPMENT_METHOD, self::API_SHIPMENT_RESOURCE, $transactionId, $data);

        return true;
    }

    /**
     * @param $transactionId
     * @param $reason
     * @param $amount
     * @param $date
     * @return bool
     */
    public function cancelOrder($transactionId, $reason, $amount, $date) {
        if (!in_array($reason, $this->cancelReasons)) {
            $this->throwException(sprintf("%s Unknown Cancel Reason: '%s'; Available Reasons: %s", self::LOG_ID, $reason, implode(",", $this->cancelReasons)));
        }

        $data = array(
            'grund' => $reason,
            'betrag' => $amount,
            'datum' => $date
        );

        $this->callApi(self::API_CANCEL_METHOD, self::API_CANCEL_RESOURCE, $transactionId, $data);

        return true;
    }
}