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
            ],
            'user_data' => $this->getOrderUserData($order)
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

    /**
     *
     */
    public function getOrderUserData(?OrderInterface $order): ?array
    {
        $address = $order->getBillingAddress() ?: $order->getShippingAddress();

        if (!$address) {
            return null;
        }

        return [
            'email' => $order->getCustomerEmail(),
            'tel' => $this->getTelephone($address->getTelephone()),
            'address' => [
                'first_name' => $order->getCustomerFirstname(),
                'last_name' => $order->getCustomerLastname(),
                'street' => implode(
                    ', ',
                    array_map(
                        'trim',
                        array_filter(
                            array_unique(
                                $address->getStreet()
                            )
                        )
                    )
                ),
                'city' => $address->getCity(),
                'region' => $address->getRegion(),
                'postal_code' => $address->getPostcode(),
                'country' => $address->getCountryId()
            ]
        ];
    }


    /**
     *
     */
    private function getTelephone($input): ?string
    {
        $tel = str_replace(' ', '', trim($input ?? ''));

        if (strpos($tel, '+') !== 0) {
            $tel = preg_replace('/^0/', '+44', $tel);
        }

        return $tel;
    }
}
