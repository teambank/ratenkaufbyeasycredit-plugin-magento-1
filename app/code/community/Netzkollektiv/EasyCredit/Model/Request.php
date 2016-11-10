<?php
class Netzkollektiv_EasyCredit_Model_Request {

    protected function _getBaseData() {
        return array(
            'shopKennung' => '2.de.9999.9999'
        );
    }

    protected function _convertAddress(Mage_Customer_Model_Address $address) {
        return array(
             'strasseHausNr' => $address->getStreetAddress(1),
             'adresszusatz' => is_array($address->getStreetAddress()) ? implode(',',array_slice($address->getStreetAddress(),1)) : '',
             'plz' => $address->getPostcode(),
             'ort' => $address->getCity(),
             'land' => $address->getCountryId()
        );
    }

    protected function _convertPersonalData(Mage_Sales_Model_Quote $quote) {
        return array(
            'anrede' => $quote->getCustomerPrefix(),
            'vorname' => $quote->getCustomerFirstname(),
            'nachname' => $quote->getCustomerLastname(),
            'geburtsdatum' => $quote->getCustomerDob(),
        );
    }

    public function getProcessRequest(Mage_Sales_Model_Quote $quote) {
        return array_merge($this->_getBaseData(), array(
           'bestellwert' => $quote->getGrandTotal(),
           'ruecksprungadressen' => array(
               'urlAbbruch' => Mage::getBaseUrl(), //'https://www.easycredit.de/ratenkauf-demoshop/content/webshop/#/payment',
               'urlErfolg' => Mage::getBaseUrl(), //'https://www.easycredit.de/ratenkauf-demoshop/content/webshop/#/success?bestaetigen=false',
               'urlAblehnung' => Mage::getBaseUrl(), //'https://www.easycredit.de/ratenkauf-demoshop/content/webshop/#payment?denied=true',
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
