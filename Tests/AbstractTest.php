<?php
/**
 *
 */
namespace FishPig\DataLayer\Tests;

abstract class AbstractTest implements \FishPig\DataLayer\Tests\TestInterface
{
    /**
     *
     */
    protected function doVariablesMatch($a, $b, string $field = null): void
    {
        $stringify = function ($v) {
            if (is_array($v)) {
                $v = md5(json_encode($v));
            }

            return (string)$v;
        };

        $aString = $stringify($a);
        $bString = $stringify($b);

        if ($aString !== $bString) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Test Failed! "%s" !== "%s". %s',
                    print_r($a, true),
                    print_r($b, true),
                    $field ? sprintf('Field = "%s"', $field) : ''
                )
            );
        }
    }
}
