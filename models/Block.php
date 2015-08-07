<?php

class Block extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $chtsht_id;
    protected $user_id;
    protected $name;
    protected $date_added;

    public function getId() {
        return $this->id;
    }

    public function getChtshtId() {
        return $this->chtsht_id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDateAdded() {
        return $this->date_added;
    }

	public function setChtshtId($chtshtId) {
		$this->chtsht_id = $chtshtId;
	}

	public function setUserId($userId) {
		$this->user_id = $userId;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setDateAdded($dateAdded) {
		$this->date_added = $dateAdded;
	}

	public function validation() {
		if (!$this->date_added)
			$this->setDateAdded(date('Y-m-d H:i:s',time()));
	}
}