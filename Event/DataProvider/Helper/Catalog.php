<?php
/**
 *
 */
namespace FishPig\DataLayer\Event\DataProvider\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\AttributeRepository;

class Catalog
{
    /**
     *
     */
    private $attributeRepository = null;

    /**
     *
     */
    public function __construct(
        AttributeRepository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     *
     */
    public function getPriceData(ProductInterface $product): array
    {
        return array_filter([
            'price' => $this->getPrice($product)
        ]);
    }

    /**
     *
     */
    public function getPrice(ProductInterface $product): ?float
    {
        /*
        if ($product->getTypeId() === 'grouped') {
            $children = $product->getTypeInstance(
                true
            )->getAssociatedProducts(
                $product
            );
            $price = null;
            foreach ($children as $child) {
                $price = $price === null ? $child->getPrice() : min($price, $child->getPrice());
            }

            return (float)$price;
        }*/

        return (float)$product->getFinalPrice() ?: null;
    }

    /**
     *
     */
    public function getCategoryData(
        ProductInterface $product,
        int $limit = 4
    ): array {
        $data = [];
        $index = 1;

        foreach ($this->getCategoryCollection($product, $limit) as $category) {
            if (in_array($category->getName(), $data)) {
                continue;
            }
            $key = 'item_category' . ($index === 1 ? '' : $index);
            $data[$key] = $category->getName();
            ++$index;
        }

        return $data;
    }

    /**
     *
     */
    public function getCategoryCollection(
        ProductInterface $product,
        int $limit = 4
    ): iterable {
        return $product->getCategoryCollection()
            ->addAttributeToSelect([
                'name'
            ])->addAttributeToFilter(
                'level',
                ['gt' => 2]
            )->addAttributeToFilter(
                'is_active',
                1
            )->setPageSize(
                $limit
            )->setOrder(
                'position',
                'desc'
            );
    }

    /**
     *
     */
    public function getBrandData(ProductInterface $product): array
    {
        return array_filter([
            'item_brand' => $this->getBrand($product)
        ]);
    }

    /**
     *
     */
    public function getBrand(ProductInterface $product): ?string
    {
       if (null === ($brandAttribute = $this->getBrandAttribute())) {
           return null;
       }

       if ($brandAttribute->usesSource()) {
           return $product->getAttributeText($brandAttribute->getAttributeCode());
       } else {
           return $product->getData($brandAttribute->getAttributeCode());
       }
    }

    /**
     *
     */
    public function getBrandAttribute(): ?Attribute
    {
        try {
            return $this->getAttributeRepository()->get('catalog_product', 'brand');
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     *
     */
    public function getAttributeRepository(): AttributeRepository
    {
        return $this->attributeRepository;
    }
}
