<?php
/**
 *
 */
namespace FishPig\DataLayer\Block;

use FishPig\DataLayer\Api\DataLayerEventDataProviderInterface;

class Event extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     *
     */
    private $dataProvider = null;

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

        return sprintf(
            "<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push(%s);
</script>",
            json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)
        );
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
