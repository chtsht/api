<?php

class ChtshtBlock extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $chtsht_id;
    protected $block_id;
    protected $sort_order;

    public function getId() {
        return $this->id;
    }

    public function getChtshtId() {
        return $this->chtsht_id;
    }

    public function getBlockId() {
        return $this->block_id;
    }

    public function getSortOrder() {
        return $this->sort_order;
    }

    public function setChtshtId($chtshtId) {
        $this->chtsht_id = $chtshtId;
    }

    public function setBlockId($blockId) {
		$this->block_id = $blockId;
    }

    public function setSortOrder($sortOrder) {
		$this->sort_order = $sortOrder;
    }
}