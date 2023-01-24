<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

use Teambank\RatenkaufByEasyCreditApiV3 as ApiV3;

class ItemBuilder {

    protected $_categoryResource;

    public function __construct() {
        $this->_categoryResource = \Mage::getResourceModel('catalog/category');
    }

    private function buildSkus($item): array
    {
        $skus = [];
        foreach (\array_filter(
            [
            'sku' => $item->getSku(),
            'ean' => $item->getEan()
            ]
        ) as $type => $sku) {
            $skus[] = new ApiV3\Model\ArticleNumberItem(
                [
                'numberType' => $type,
                'number' => $sku
                ]
            );
        }
        return $skus;
    }

    /**
     * @param $categoryIds
     * @return array|bool|null|string
     */
    private function getDeepestCategoryName($categoryIds)
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

    public function build($item)
    {
        return new ApiV3\Model\ShoppingCartInformationItem(
            [
            'productName' => $item->getName(),
            'quantity' => $item->getQty(),
            'price' => $item->getPrice(),
            'manufacturer' => $item->getProduct()->getData('manufacturer'),
            'productCategory' => $this->getDeepestCategoryName($item->getProduct()->getCategoryIds()),
            'articleNumber' => $this->buildSkus($item)
            ]
        );
    }
}
