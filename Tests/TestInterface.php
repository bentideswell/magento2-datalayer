<?php
/**
 *
 */
namespace FishPig\DataLayer\Tests;

interface TestInterface
{
    /**
     *
     */
    public function getId(): string;

    /**
     *
     */
    public function runTest(): void;
}
