<?php
class Netzkollektiv_EasyCredit_Adminhtml_Easycredit_MerchantController extends Mage_Adminhtml_Controller_Action
{
    protected function _getApi() {
        return Mage::helper('easycredit')->getMerchant();
    }

    public function transactionAction()
    {
        $transactionId = $this->getRequest()->getParam('id');
        foreach ($this->_getApi()->searchTransactions() as $transaction) {
            if ($transactionId == $transaction->vorgangskennungFachlich) {
                echo json_encode($transaction);
            }
        }
        exit;
    }

    public function transactionsAction()
    {
        $transactions = [];
        foreach ($this->_getApi()->searchTransactions() as $transaction) {
            $transactions[] = (array)$transaction;
        }

        echo json_encode($transactions);
        exit;
    }

    public function postTransactionAction() {
        $client = $this->_getApi();
        $params = json_decode($this->getRequest()->getRawBody());

        try {
            switch ($params->status) {
                case "LIEFERUNG":
                    $client->confirmShipment($params->id);
                    $success = true;
                    break;
                case "WIDERRUF_VOLLSTAENDIG":
                case "WIDERRUF_TEILWEISE":
                case "RUECKGABE_GARANTIE_GEWAEHRLEISTUNG":
                case "MINDERUNG_GARANTIE_GEWAEHRLEISTUNG":
                    $client->cancelOrder(
                        $params->id,
                        $params->status,
                        \DateTime::createFromFormat('Y-d-m', $params->date),
                        $params->amount
                    );
                    break;
                default:
                    throw new \Exception('Status "'.$params->status.'" does not have any action');
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;        
    }
}