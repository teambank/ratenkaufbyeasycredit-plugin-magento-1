<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

class ShippingAddress extends Address implements \Netzkollektiv\EasyCreditApi\Rest\ShippingAddressInterface {

    public function getIsPackstation() {
        $street = $this->_address->getStreet();
        if (is_array($street)) {
            $street = implode(' ',$street);
        }
        $street.= $this->getStreetAdditional();
        return stripos($street, 'packstation');
    }
}
