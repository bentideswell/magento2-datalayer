<?php
/**
 *
 */
namespace FishPig\DataLayer\Tests\Event;

class ViewItem extends \FishPig\DataLayer\Tests\AbstractTest
{
    /**
     *
     */
    private $productFactory = null;
    private $productCollectionFactory = null;
    private $dataProvider = null;
    private $blockFactory = null;
    private $productVisibility = null;
    private $catalogHelper = null;

    /**
     *
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \FishPig\DataLayer\Event\DataProvider\ViewItem $dataProvider,
        \FishPig\DataLayer\Block\Event\ViewItemFactory $blockFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \FishPig\DataLayer\Event\DataProvider\Helper\Catalog $catalogHelper
    ) {
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->dataProvider = $dataProvider;
        $this->blockFactory = $blockFactory;
        $this->productVisibility = $productVisibility;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     *
     */
    public function getId(): string
    {
        return \FishPig\DataLayer\Event\DataProvider\ViewItem::EVENT;
    }

    /**
     *
     */
    public function runTest(): void
    {
        $product = $this->productFactory->create();
        $inputData = [
            'id' => 1,
            'sku' => 'SKU-' . rand(1, 99999),
            'name' => 'Product ' . rand(1, 9999),
            'final_price' => rand(10, 50) + (rand(1, 99) / 100)
        ];

        $pushData = $this->dataProvider->setProduct(
            $product->addData($inputData)
        )->getData();

        $this->doVariablesMatch(
            1,
            count($pushData['ecommerce']['items']),
            'items_count'
        );

        foreach ([
            'item_name' => 'name',
            'item_id' => 'sku',
        ] as $a => $b) {
            $this->doVariablesMatch(
                $pushData['ecommerce']['items'][0][$a],
                $inputData[$b],
                $a
            );
        }

        // Now generate event block and check output
        $eventHtml = $this->blockFactory->create()->setDataProvider(
            $this->dataProvider
        )->toHtml();

        foreach ($pushData['ecommerce'] as $key => $value) {
            if (!is_array($value)) {
                $this->doVariablesMatch(
                    1,
                    (int)(strpos($eventHtml, (string)$value) !== false),
                    'eventHTML.contains($' . $key . ')'
                );
            }
        }

        $products = $this->productCollectionFactory->create();
        $products->addAttributeToFilter('status', 1);
        $products->setOrder('entity_id', 'desc');
        $products->setVisibility(
            $this->productVisibility->getVisibleInSiteIds()
        );
        $products->setPageSize(10);

        foreach ($products as $product) {
            $product = $this->productFactory->create()->load($product->getId());
            $pushData = $this->dataProvider->setProduct(
                $product
            )->getData();

            $this->doVariablesMatch(
                1,
                count($pushData['ecommerce']['items']),
                'items_count_real'
            );

            $expected = [
                'item_name' => $product->getName()
            ];

            foreach ($expected as $key => $value) {
                $this->doVariablesMatch(
                    $value,
                    $pushData['ecommerce']['items'][0][$key],
                    $key
                );
            }

            if (count($this->catalogHelper->getCategoryCollection($product)) > 0) {
                $this->doVariablesMatch(
                    0,
                    (int)empty($pushData['ecommerce']['items'][0]['item_category']),
                    'item_category'
                );
            }

            $this->doVariablesMatch(
                0,
                (int)empty($pushData['ecommerce']['items'][0]['item_brand']),
                'item_brand'
            );
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
