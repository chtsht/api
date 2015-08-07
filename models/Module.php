<?php

class Module extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $module;

    public function getId() {
        return $this->id;
    }

    public function getModule() {
        return $this->module;
    }

	public function setModule($module) {
		$this->module = $module;
	}
}