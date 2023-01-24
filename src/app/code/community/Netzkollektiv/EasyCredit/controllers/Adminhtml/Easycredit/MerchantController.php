<?php
use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

class Netzkollektiv_EasyCredit_Adminhtml_Easycredit_MerchantController extends Mage_Adminhtml_Controller_Action
{
    private function sendJsonResponseFromException(ApiException $response) : void
    {
        $this->sendJsonResponse((string) $response->getResponseBody(), $response->getCode());
    }

    private function sendJsonResponse(string $body, int $statusCode = 200) : void
    {
        $this->getResponse()->setHeader('Content-Type', 'application/json', true)
            ->setBody($body)
            ->setHttpResponseCode($statusCode);
            $this->getResponse()->sendResponse();
        exit;
    }

    private function getBodyParams () {
        $interpreter = Mage_Api2_Model_Request_Interpreter::factory('application/json');
        return $interpreter->interpret((string)$this->getRequest()->getRawBody());
    }

    public function transactionsAction()
    {
        try {
            $transactionIds = $this->getRequest()->getParam('ids');

            $response = Mage::helper('easycredit')
                ->getTransactionApi()
                ->apiMerchantV3TransactionGet(null, null,  null, 100, null, null, null, null, ['tId' => $transactionIds]);
            $this->sendJsonResponse($response);
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }

    public function transactionAction()
    {
        try {
            $transactionId = $this->getRequest()->getParam('id');

            $response = Mage::helper('easycredit')
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdGet($transactionId);
            $this->sendJsonResponse($response);
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }

    public function captureAction()
    {
        try {
            $transactionId = $this->getRequest()->getParam('id');
            $bodyParams = $this->getBodyParams();

            Mage::helper('easycredit')
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdCapturePost(
                    $transactionId,
                    new CaptureRequest(['trackingNumber' => $bodyParams['trackingNumber'] ?? null])
                );
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }

    public function refundAction()
    {
        try {
            $transactionId = $this->getRequest()->getParam('id');
            $bodyParams = $this->getBodyParams();

            Mage::helper('easycredit')
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdRefundPost(
                    $transactionId,
                    new RefundRequest(['value' => $bodyParams['value']])
                );
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }
}