<?php

use Phalcon\Mvc\Model,
	Phalcon\Mvc\Model\Message,
	Phalcon\Mvc\Model\Validator\StringLength,
	Phalcon\Mvc\Model\Validator\Uniqueness;

class User extends Model
{
    protected $id;
    protected $display_name;
    protected $avatar;
    protected $status;
    protected $date_added;

    public function getId() {
        return $this->id;
    }

    public function getDisplayName() {
        return $this->display_name;
    }

    public function getAvatar() {
        return $this->avatar;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getDateAdded() {
        return $this->date_added;
    }

    public function setDisplayName($displayName) {
        $this->display_name = $displayName;
    }

	public function setDateAdded($dateAdded) {
		$this->date_added = $dateAdded;
	}

    public function setAvatar($avatar) {
        $this->avatar = $avatar;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

	public function validation() {
		if (!$this->date_added)
			$this->setDateAdded(date('Y-m-d H:i:s',time()));

		$this->validate(new StringLength(
			array(
				"field"				=> "display_name",
				"min"				=> "3",
				"max"				=> "20",
				"messageMinimum"	=> "Display name must be at least 3 characters long",
				"messageMaximum"	=> "Display name must be less than 20 characters long"
			)
		));

		$this->validate(new Uniqueness(
			array(
				"field"   => "display_name",
				"message" => "Display name already taken"
			)
		));

		return $this->validationHasFailed() != true;
	}
}