<?php
class Netzkollektiv_EasyCredit_CredentialsController extends Mage_Core_Controller_Front_Action {
    public function verifyAction() {
        $params = $this->getRequest()->getParams();

        $this->getResponse()->setHeader('Content-type', 'application/json');

        if (!isset($params['apiKey']) || !isset($params['apiToken'])) {
            $this->getResponse()->setBody(json_encode(["status" => false, "errorMessage" => 'values missing']));
            return;
        }

        $apiKey = $params['apiKey'];
        $apiToken = $params['apiToken'];

        /**
         * @var $api Netzkollektiv_EasyCredit_Model_Api
         */
        $api = Mage::getSingleton('easycredit/api');

        if ($api->verifyCredentials($apiKey, $apiToken)) {
            $this->getResponse()->setBody(json_encode(["status" => true, "errorMessage" => '']));
        } else {
            $this->getResponse()->setBody(json_encode(["status" => false, "errorMessage" => 'Credentials invalid. Please check your input!']));
        }
    }
}