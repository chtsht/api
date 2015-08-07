<?php

use Phalcon\Mvc\Model\QueryInterface,
	Phalcon\Mvc\Model\Manager;

class Element extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $type_id;
    protected $block_id;
    protected $user_id;
    protected $content;
    protected $description;
    protected $date_added;

    public function getId() {
        return $this->id;
    }

    public function getTypeId() {
        return $this->type_id;
    }

    public function getBlockId() {
        return $this->block_id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getContent() {
        return $this->content;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDateAdded() {
        return $this->date_added;
    }

	public function setTypeId($typeId) {
		$this->type_id = $typeId;
	}

	public function setBlockId($blockId) {
		$this->block_id = $blockId;
	}

	public function setUserId($userId) {
		$this->user_id = $userId;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function setDateAdded($dateAdded) {
		$this->date_added = $dateAdded;
	}

    public function getChtshtId() {
		$result = $this->getModelsManager()->executeQuery("SELECT c.id FROM Chtsht AS c JOIN Block AS b ON b.chtsht_id = c.id JOIN Element AS e ON e.block_id = b.id WHERE e.id = {$this->id}");
		return $result[0]->id;
    }

	public function validation() {
		if (!$this->date_added)
			$this->setDateAdded(date('Y-m-d H:i:s',time()));
	}
}