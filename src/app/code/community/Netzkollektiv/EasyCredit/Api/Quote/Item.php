<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

class Item implements \Netzkollektiv\EasyCreditApi\Rest\ItemInterface {

    protected $_item = null;

    public function __construct(
        $item,
        $storeManager,
        $categoryResource
    ) {
        $this->_item = $item;
        $this->_categoryResource = $categoryResource;
        $this->_storeManager = $storeManager;
    }

    public function getSku() {
        return $this->_item->getSku();
    }

    public function getName() {
        return $this->_item->getName();
    }

    public function getQty() {
        return $this->_item->getQty();
    }

    public function getPrice() {
        return $this->_item->getPrice();
    }

    public function getManufacturer() {
        return $this->_item->getProduct()->getData('manufacturer');
    }

    public function getCategory() {
        return $this->_getDeepestCategoryName(
            $this->_item->getProduct()->getCategoryIds()
        );
    }

    /**
     * @param $categoryIds
     * @return array|bool|null|string
     */
    protected function _getDeepestCategoryName($categoryIds)
    {
        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $categoryId = end($categoryIds);
            return $this->_categoryResource->getAttributeRawValue(
                $categoryId,
                'name',
                \Mage::app()->getStore()->getId()
            );
        }
        return null;
    }
}
