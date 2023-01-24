<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

class AddressBuilder {
    protected $address = array();

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function build($address)
    {
        $this->address['firstName'] = $address->getFirstname();
        $this->address['lastName'] = $address->getLastname();
        $this->address['address'] = (is_array($address->getStreet()))
            ? $address->getStreet()[0]
            : $address->getStreet();
        $this->address['additionalAddressInformation'] = is_array($address->getStreet()) && isset($address->getStreet()[1]) ? $address->getStreet()[1] : null;
        $this->address['zip'] = $address->getPostcode();
        $this->address['city'] = $address->getCity();
        $this->address['country'] = $address->getCountryId();

        return $this->address;
    }
}
