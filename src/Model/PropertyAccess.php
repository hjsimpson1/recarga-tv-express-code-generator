<?php


namespace CViniciusSDias\RecargaTvExpress\Model;

trait PropertyAccess
{
    public function __get(string $property)
    {
        return $this->$property;
    }
}
