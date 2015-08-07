<?php

class BlockElement extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $block_id;
    protected $element_id;
	protected $sort_order;

    public function getId() {
        return $this->id;
    }

    public function getBlockId() {
        return $this->block_id;
    }

    public function getElementId() {
        return $this->element_id;
    }

	public function getSortOrder() {
		return $this->sort_order;
	}

    public function setBlockId($blockId) {
        $this->block_id = $blockId;
    }

    public function setElementId($elementId) {
        $this->element_id = $elementId;
    }

	public function setSortOrder($sortOrder) {
		$this->sort_order = $sortOrder;
	}
}