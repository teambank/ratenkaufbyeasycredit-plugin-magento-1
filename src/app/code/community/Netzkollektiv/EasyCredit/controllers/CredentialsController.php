<?php
use Teambank\RatenkaufByEasyCreditApiV3\Integration\ApiCredentialsInvalidException;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\ApiCredentialsNotActiveException;

class Netzkollektiv_EasyCredit_CredentialsController extends Mage_Core_Controller_Front_Action {
    public function verifyAction() {
        $params = $this->getRequest()->getParams();

        $this->getResponse()->setHeader('Content-type', 'application/json');

        $apiKey = isset($params['apiKey']) ? $params['apiKey'] : '';
        $apiToken = isset($params['apiToken']) ? $params['apiToken'] : '';
        $apiSignature = isset($params['apiSignature']) ? $params['apiSignature'] : '';

        try {
            Mage::helper('easycredit')
                ->getCheckout()
                ->verifyCredentials($apiKey, $apiToken, $apiSignature);
            $this->getResponse()->setBody(json_encode(array(
                "status" => true
            )));
        } catch (ApiCredentialsInvalidException $e) {
            $this->getResponse()->setBody(json_encode(array(
                "status" => false, 
                "errorMessage" => Mage::helper('easycredit')->__('Credentials incorrect. Please review the inserted values and try again.')
            )));
        } catch (ApiCredentialsNotActiveException $e) {
            $this->getResponse()->setBody(json_encode(array(
                "status" => false, 
                "errorMessage" => Mage::helper('easycredit')->__('The provided API credentials are valid, but not yet activated')
            )));
        } catch (\Throwable $e) {
            Mage::logException($e);
            $this->getResponse()->setBody(json_encode(array(
                "status" => false, 
                "errorMessage" => Mage::helper('easycredit')->__('Error verifying credentials. Please try again later.')
            )));
        }
    }
}
