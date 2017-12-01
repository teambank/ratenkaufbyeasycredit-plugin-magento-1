<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

class Address implements \Netzkollektiv\EasyCreditApi\Rest\AddressInterface {
    protected $_address = array();

    public function __construct($address) {
        $this->_address = $address;
    }

    public function getFirstname() {
        return $this->_address->getFirstname();
    }

    public function getLastname() {
        return $this->_address->getLastname();
    }

    public function getStreet() {
        return (is_array($this->_address->getStreet())) ?
            $this->_address->getStreet()[0]
            : $this->_address->getStreet();
    }

    public function getStreetAdditional() {
        $street = $this->_address->getStreet();
        return is_array($street) && isset($street[1]) ? $street[1] : null;
    }

    public function getPostcode() {
        return $this->_address->getPostcode();
    }

    public function getCity() {
        return $this->_address->getCity();
    }

    public function getCountryId() {
        return $this->_address->getCountryId();
    }
}
