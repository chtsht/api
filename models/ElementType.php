<?php

class ElementType extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $type;

    public function getId() {
        return $this->id;
    }

    public function getType() {
        return $this->type;
    }
}