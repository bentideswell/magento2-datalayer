<?php
/**
 *
 */
namespace FishPig\DataLayer\Tests;

class Pool
{
    /**
     *
     */
    private $tests = [];

    /**
     *
     */
    public function __construct(array $tests = [])
    {
        foreach ($tests as $test) {
            if ($test instanceof \FishPig\DataLayer\Tests\TestInterface) {
                $this->tests[$test->getId()] = $test;
            }
        }
    }

    /**
     *
     */
    public function getAll(): iterable
    {
        return $this->tests;
    }

    /**
     *
     */
    public function get(string $id): ?TestInterface
    {
        foreach ($this->getAll() as $test) {
            if ($id === $test->getId()) {
                return $test;
            }
        }

        return null;
    }
}
