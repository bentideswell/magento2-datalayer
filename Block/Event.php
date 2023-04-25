<?php
/**
 *
 */
namespace FishPig\DataLayer\Block;

use FishPig\DataLayer\Api\DataLayerEventDataProviderInterface;

class Event extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var ?DataLayerEventDataProviderInterface
     */
    private $dataProvider = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        ?DataLayerEventDataProviderInterface $dataProvider = null,
        array $data = []
    ) {
        $this->setDataProvider($dataProvider);
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _toHtml()
    {
        if (($dataProvider = $this->getDataProvider()) === null) {
            return '';
        }

        if (($data = $dataProvider->getData()) === null) {
            return '';
        }

        $data = $this->_prepareData($data);

        return sprintf(
            "<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push(%s);
</script>",
            json_encode(
                $data,
                JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
            )
        );
    }

    /**
     *
     */
    protected function _prepareData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->_prepareData($value);
            } else {
                $data[$key] = (string)$value;
            }
        }

        return $data;
    }

    /**
     *
     */
    public function setDataProvider(
        DataLayerEventDataProviderInterface $dataProvider
    ): self {
        $this->dataProvider = $dataProvider;
        return $this;
    }

    /**
     *
     */
    public function getDataProvider(): ?DataLayerEventDataProviderInterface
    {
        return $this->dataProvider;
    }
}
