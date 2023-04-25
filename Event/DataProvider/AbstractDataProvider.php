<?php
/**
 *
 */
namespace FishPig\DataLayer\Event\DataProvider;

use FishPig\DataLayer\Api\DataLayerEventDataProviderInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

abstract class AbstractDataProvider implements DataLayerEventDataProviderInterface, ArgumentInterface
{
    /**
     *
     */
    public function getId(): string
    {
        return static::EVENT;
    }
}
