<?php

/**
 * in order to handle dynamic fields of BaseEntity and its reflection class
 * we need to have a Fake class to fill Entity Classdata ReflFields
 *
 */
namespace Osds\Api\Framework\Symfony;

class FakeReflectionFactory {

    public $name;

    public $value;

    public $class;

    public function __construct($class, $name, $value)
    {
        $this->class = $class;
        $this->name = $name;
        $this->value = $value;

    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

}