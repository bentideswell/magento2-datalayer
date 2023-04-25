<?php
/**
 *
 */
namespace FishPig\DataLayer\Event\DataProvider;

abstract class AbstractDataProvider implements \FishPig\DataLayer\Api\DataLayerEventDataProviderInterface, \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     *
     */
    public function getId(): string
    {
        return static::EVENT;
    }
}
