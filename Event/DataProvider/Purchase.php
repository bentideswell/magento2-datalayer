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
    public function getData(): ?array
    {
        if (($order = $this->getOrder()) === null) {
            return null;
        }

        if (!$order->getId()) {
            return null;
        }

        $data = [
            'event' => self::EVENT,
            'ecommerce' => [
                'currency' => $order->getOrderCurrencyCode(),
                'value' => (float)$order->getGrandTotal(),
                'tax' => (float)$order->getTaxAmount(),
                'shipping' =>  (float)$order->getShippingAmount(),
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
                'price' => (float)$orderItem->getPrice(),
                'quantity' => (int)$orderItem->getQtyOrdered(),
            ],
            $this->getOrderItemCategories($orderItem),
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
    public function getOrderItemCategories(OrderItemInterface $orderItem): array
    {
        $data = [];
        $categories = $orderItem->getProduct()
            ->getCategoryCollection()
            ->addAttributeToSelect([
                'name'
            ])->addAttributeToFilter(
                'level',
                ['gt' => 2]
            )->addAttributeToFilter(
                'is_active',
                1
            )->setPageSize(
                4
            )->setOrder(
                'position',
                'desc'
            );

        if (count($categories) === 0) {
            return $data;
        }

        $index = 1;

        foreach ($categories as $category) {
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
