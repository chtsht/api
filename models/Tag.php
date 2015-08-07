<?php
use Phalcon\Mvc\Model,
	Phalcon\Mvc\Model\Message,
	Phalcon\Mvc\Model\Validator\InclusionIn,
	Phalcon\Mvc\Model\Validator\Uniqueness;

class Tag extends Model
{
    protected $id;
    protected $name;
    protected $date_added;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDateAdded() {
        return $this->date_added;
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

		$this->validate(new Uniqueness(
			array(
				"field"   => "name",
				"message" => "Name must be unique"
			)
		));

		return $this->validationHasFailed() != true;
	}
}