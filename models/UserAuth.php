<?php

use Phalcon\Mvc\Model,
	Phalcon\Mvc\Model\Message,
	Phalcon\Mvc\Model\Validator\StringLength,
	Phalcon\Mvc\Model\Validator\Uniqueness;

class UserAuth extends Model
{
    protected $id;
    protected $user_id;
    protected $identity;
    protected $access_token;
    protected $date_added;

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getIdentity() {
        return $this->identity;
    }

    public function getAccessToken() {
        return $this->access_token;
    }

    public function getDateAdded() {
        return $this->date_added;
    }

    public function setUserId($userId) {
        $this->user_id = $userId;
    }

	public function setIdentity($identity) {
		$this->identity = $identity;
	}

	public function setAccessToken($accessToken) {
		$this->access_token = $accessToken;
	}

	public function setDateAdded($dateAdded) {
		$this->date_added = $dateAdded;
	}

	public function validation() {
		if (!$this->date_added)
			$this->setDateAdded(date('Y-m-d H:i:s',time()));

		$this->validate(new Uniqueness(
			array(
				"field"   => "identity",
				"message" => "Identity already exists"
			)
		));

		return $this->validationHasFailed() != true;
	}
}