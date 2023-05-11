<?php
/**
 *
 */
namespace FishPig\DataLayer\Event\DataProvider\Helper;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Sales
{
    /**
     *
     */
    private $catalogHelper = null;

    /**
     *
     */
    public function __construct(
        Catalog $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }

    /**
     *
     */
    public function getCategoryData(
        OrderItemInterface $orderItem,
        int $limit = 4
    ): array {
        return $this->catalogHelper->getCategoryData(
            $orderItem->getProduct(),
            $limit
        );
    }

    /**
     *
     */
    public function getBrandData(OrderItemInterface $orderItem): array
    {
        return $this->catalogHelper->getBrandData(
            $orderItem->getProduct()
        );
    }
}
