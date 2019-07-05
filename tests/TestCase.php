<?php

namespace Angecode\IproSoftware\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param $object
     * @param $property
     * @param $value
     *
     * @throws \ReflectionException
     */
    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

    /**
     * @param $object
     * @param $propertyName
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function getProtectedProperty($object, $propertyName)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    protected function arrayKeyFirst(array $array)
    {
        // Support only for php >= 7.3
        //$someMethod = array_key_first($predefinedMethods);
        reset($array);

        return key($array);
    }
}
