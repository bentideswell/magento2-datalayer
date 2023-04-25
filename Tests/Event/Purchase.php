<?php
/**
 *
 */
namespace FishPig\DataLayer\Tests\Event;

class Purchase extends \FishPig\DataLayer\Tests\AbstractTest
{
    /**
     *
     */
    private $orderFactory = null;
    private $orderItemFactory = null;
    private $productFactory = null;
    private $purchaseEventDataProvider = null;
    private $eventBlockFactory = null;

    /**
     *
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \FishPig\DataLayer\Event\DataProvider\Purchase $purchaseEventDataProvider,
        \FishPig\DataLayer\Block\EventFactory $eventBlockFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->productFactory = $productFactory;
        $this->purchaseEventDataProvider = $purchaseEventDataProvider;
        $this->eventBlockFactory = $eventBlockFactory;
    }

    /**
     *
     */
    public function getId(): string
    {
        return \FishPig\DataLayer\Event\DataProvider\Purchase::EVENT;
    }

    /**
     *
     */
    public function runTest(): void
    {
        $order = $this->orderFactory->create();
        $inputData = [
            'increment_id' => '0000000' . rand(1, 9),
            'order_currency_code' => 'XYZ',
            'grand_total' => $this->getRandomPrice(50, 99),
            'tax_amount' => $this->getRandomPrice(6, 9),
            'shipping_amount' => $this->getRandomPrice(1, 5),
            'coupon_code' => 'RANDOM_COUPON'
        ];

        $order->addData($inputData);


        $parentProduct = $this->productFactory->create()
            ->setId(1)
            ->setSku('SKU1')
            ->setName('Parent Product')
            ->setPrice(9.99);

        $childProduct = $this->productFactory->create()
            ->setId(2)
            ->setSku('SKU2')
            ->setName('Child Product');

        $orderItems = [];
        foreach ([$parentProduct, $childProduct] as $product) {
            $orderItems[] = $this->orderItemFactory->create()
                ->setId(count($orderItems) + 1)
                ->setName($product->getName())
                ->setProductId($product->getId())
                ->setSku($product->getSku())
                ->setPrice($product->getPrice())
                ->setProduct(
                    $product
                )->setQtyOrdered(
                    1
                );
        }

        // Set relationship for order items
        $orderItems[1]->setParentItem($orderItems[0])
            ->setParentItemId($orderItems[0]->getId());

        $order->setData('items', $orderItems);

        $pushData = $this->purchaseEventDataProvider->setOrder($order)->getData();

        foreach ([
            'transaction_id' => 'increment_id',
            'currency' => 'order_currency_code',
            'value' => 'grand_total',
            'tax' => 'tax_amount',
            'shipping' => 'shipping_amount',
            'coupon' => 'coupon_code'
        ] as $a => $b) {
            $this->doVariablesMatch(
                $pushData['ecommerce'][$a],
                $inputData[$b],
                $a
            );
        }

        $this->doVariablesMatch(
            1,
            count($pushData['ecommerce']['items']),
            'order_item_count'
        );

        $pushedItemData = $pushData['ecommerce']['items'][0];

        foreach ([
            'item_name' => $parentProduct->getName(),
            'item_id' => $parentProduct->getSku(),
            'price' => $parentProduct->getPrice(),
            'quantity' => $orderItems[0]->getQtyOrdered(),
            'item_variant' => $childProduct->getName()
        ] as $a => $b) {
            $this->doVariablesMatch($pushedItemData[$a], $b, $a);
        }

        // Now generate event block and check output
        $eventHtml = $this->eventBlockFactory->create()->setDataProvider(
            $this->purchaseEventDataProvider
        )->toHtml();

        foreach ($pushData['ecommerce'] as $key => $value) {
            if (!is_array($value)) {
                $this->doVariablesMatch(
                    1,
                    (int)(strpos($eventHtml, $value) !== false),
                    'eventHTML.contains($' . $key . ')'
                );
            }
        }
    }

    /**
     *
     */
    private function getRandomPrice(int $from, int $to): float
    {
        $value = rand($from, $to);

        if ($pence = rand(0, 99)) {
            $value = (float)$value + $pence/100;
        }

        return (float)$value;
    }
}
