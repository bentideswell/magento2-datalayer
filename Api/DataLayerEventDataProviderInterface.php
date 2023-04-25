<?php
/**
 *
 */
namespace FishPig\DataLayer\Api;

interface DataLayerEventDataProviderInterface
{
    /**
     *
     */
    public function getId(): string;

    /**
     *
     */
    public function getData(): ?array;
}
