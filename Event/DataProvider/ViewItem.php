<?php
/**
 *
 */
namespace FishPig\DataLayer\Event\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;

class ViewItem extends AbstractDataProvider
{
    /**
     *
     */
    const EVENT = 'view_item';

    /**
     *
     */
    private $coreRegistry = null;

    /**
     *
     */
    private $catalogHelper = null;

    /**
     *
     */
    private $product = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \FishPig\DataLayer\Event\DataProvider\Helper\Catalog $catalogHelper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     *
     */
    public function getData(): ?array
    {
        if (null === ($productData = $this->getProductData())) {
            return null;
        }

        $data = [
            'event' => self::EVENT,
            'ecommerce' => [
                'items' => [$productData]
            ]
        ];

        return $data;
    }

    /**
     *
     */
    public function getProductData(): ?array
    {
        if (($product = $this->getProduct()) === null) {
            return null;
        }

        return array_merge(
            [
                'item_name' => $product->getName(),
                'item_id' => $product->getSku(),
            ],
            $this->catalogHelper->getPriceData($product),
            $this->catalogHelper->getCategoryData($product),
            $this->catalogHelper->getBrandData($product)
        );
    }

    /**
     *
     */
    public function getProduct(): ?ProductInterface
    {
        return $this->product ?? $this->coreRegistry->registry('product');
    }

    /**
     *
     */
    public function setProduct(?ProductInterface $product): self
    {
        $this->product = $product;
        return $this;
    }
}
