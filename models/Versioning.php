<?php

class Versioning extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $user_id;
    protected $module_id;
    protected $chtsht_id;
    protected $data;
    protected $date_added;

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getModuleId() {
        return $this->module_id;
    }

    public function getChtshtId() {
        return $this->chtsht_id;
    }

    public function getData() {
        return $this->data;
    }

    public function getDateAdded() {
        return $this->date_added;
    }

    public function setUserId($userId) {
        $this->user_id = $userId;
    }

    public function setModuleId($moduleId) {
        $this->module_id = $moduleId;
    }

	public function setChtshtId($chtshtId) {
		$this->chtsht_id = $chtshtId;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function setDateAdded($dateAdded) {
		$this->date_added = $dateAdded;
	}

	public function validation() {
		if (!$this->date_added)
			$this->setDateAdded(date('Y-m-d H:i:s',time()));
	}
}