<?php
use Phalcon\Mvc\Model;

class Session extends Model
{
    protected $id;
    protected $user_id;
    protected $token;
    protected $date_added;
    protected $last_active;

    public function getId() {
        return $this->id;
    }

	public function getUserId() {
		return $this->user_id;
	}

	public function getToken() {
		return $this->token;
	}

	public function getDateAdded() {
		return $this->date_added;
	}

	public function getLastActive() {
		return $this->last_active;
	}

	public function setUserId($userId) {
		$this->user_id = $userId;
	}

	public function setToken($token) {
		$this->token = $token;
	}

	public function setDateAdded($dateAdded) {
		$this->date_added = $dateAdded;
	}

	public function setLastActive($lastActive) {
		$this->last_active = $lastActive;
	}

	public function updateLastActive() {
		$this->setLastActive(date('Y-m-d H:i:s',time()));
		$this->save();
	}

	public function validation() {
		if (!$this->date_added)
			$this->setDateAdded(date('Y-m-d H:i:s',time()));

		if (!$this->last_active)
			$this->setLastActive(date('Y-m-d H:i:s',time()));
	}
}