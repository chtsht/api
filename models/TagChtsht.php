<?php

class TagChtsht extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $tag_id;
    protected $chtsht_id;

    public function getId() {
        return $this->id;
    }

    public function getTagId() {
        return $this->tag_id;
    }

    public function getChtshtId() {
        return $this->chtsht_id;
    }

    public function setTagId($tagId) {
        $this->tag_id = $tagId;
    }

    public function setChtshtId($chtshtId) {
        $this->chtsht_id = $chtshtId;
    }
}