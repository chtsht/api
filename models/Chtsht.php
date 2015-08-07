<?php
use Phalcon\Mvc\Model,
	Phalcon\Mvc\Model\Message,
	Phalcon\Mvc\Model\Validator\InclusionIn,
	Phalcon\Mvc\Model\Validator\Uniqueness;

class Chtsht extends Model
{
    protected $id;
    protected $name;
    protected $url;
    protected $user_id;
    protected $description;
    protected $date_added;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDateAdded() {
        return $this->date_added;
    }

	public function setName($name) {
		$this->name = $name;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function setDateAdded($dateAdded) {
		$this->date_added = $dateAdded;
	}

    public function setUserId($userId) {
        $this->user_id = $userId;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function validation() {
        $this->validate(new Uniqueness(
            array(
                "field"   => "url",
                "message" => "Url must be unique"
            )
        ));

        return $this->validationHasFailed() != true;
    }

	public function beforeSave() {
		if (!$this->date_added)
			$this->setDateAdded(date('Y-m-d H:i:s',time()));
	}

    public function initialize() {
        $this->hasMany("id", "Block", "chtsht_id");
    }
}