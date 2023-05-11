<?php
/**
 *
 */
namespace FishPig\DataLayer\Event\DataProvider;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class Purchase extends AbstractDataProvider
{
    /**
     *
     */
    const EVENT = 'purchase';

    /**
     *
     */
    private $order = null;

    /**
     *
     */
    private $salesHelper = null;

    /**
     *
     */
    public function __construct(
        \FishPig\DataLayer\Event\DataProvider\Helper\Sales $salesHelper
    ) {
        $this->salesHelper = $salesHelper;
    }

    /**
     *
     */
    public function getData(): ?array
    {
        if (($order = $this->getOrder()) === null) {
            return null;
        }

        if (!$order->getId() && !$order->getIncrementId()) {
            return null;
        }

        $data = [
            'event' => self::EVENT,
            'ecommerce' => [
                'currency' => $order->getOrderCurrencyCode(),
                'value' => (float)$order->getGrandTotal(),
                'tax' => (float)$order->getTaxAmount(),
                'shipping' =>  (float)$order->getShippingInclTax(),
                'transaction_id' => $order->getIncrementId(),
                'coupon' => $order->getCouponCode() ?? '',
                'items' => []
            ]
        ];

        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }

            if ($itemData = array_filter(
                $this->getOrderItemData($orderItem)
            )) {
                $data['ecommerce']['items'][] = $itemData;
            }
        }

        return $data;
    }

    /**
     *
     */
    public function getOrderItemData(OrderItemInterface $orderItem): array
    {
        return array_merge(
            [
                'item_name' => $orderItem->getName(),
                'item_id' => $orderItem->getSku(),
                'price' => (float)$orderItem->getPriceInclTax(),
                'quantity' => (int)$orderItem->getQtyOrdered(),
            ],
            $this->salesHelper->getCategoryData($orderItem),
            $this->salesHelper->getBrandData($orderItem),
            $this->getOrderItemVariant($orderItem)
        );
    }

    /**
     *
     */
    public function getOrderItemVariant(OrderItemInterface $orderItem): array
    {
        if ($childItems = $orderItem->getChildrenItems()) {
            foreach ($childItems as $childItem) {
                return [
                    'item_variant' => $childItem->getName()
                ];
            }
        }

        if ($options = $orderItem->getProductOptions()) {
            if (!empty($options['options'])) {
                foreach ($options['options'] as $option) {
                    return [
                        'item_variant' => $option['print_value'] ?? $option['value']
                    ];
                }
            }
        }

        return [];
    }

    /**
     *
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     *
     */
    public function setOrder(?OrderInterface $order): self
    {
        $this->order = $order;
        return $this;
    }
}
