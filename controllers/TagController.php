<?php

class TagController extends \Phalcon\Mvc\Controller
{
	protected $Tag;

	public function get($id)
	{
		$this->Tag = Tag::findFirst($id);

		if ($this->Tag == false) {
			$this->response->setJsonContent(array('status' => 'NOT-FOUND'));
			return $this->response;
		}
		
		$this->response->setJsonContent(array(
			'status'=>'OK',
			'tag'=>$this->buildData()
		));

		return $this->response;
	}

	public function getAll()
	{
		$tags = array();
		$results = $this->modelsManager->executeQuery("
			SELECT Tag.id, Tag.name, Tag.date_added, count(TagChtsht.id) as count
			FROM Tag
			JOIN TagChtsht ON TagChtsht.tag_id = Tag.id
			GROUP BY Tag.id
			ORDER BY count DESC");

		foreach ($results as $tag) {
			$tags[] = array(
				'id'=>$tag->id,
				'name'=>$tag->name,
				'date_added'=>$tag->date_added,
				'count'=>$tag->count
			);
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'tags'=>$tags
		));

		return $this->response;
	}

	public function getByName($name)
	{
		if (!$this->Tag = Tag::findFirst(['name = :name:','bind'=>['name'=>$name]])) {
			$this->response->setJsonContent(array('status' => 'NOT-FOUND'));
			return $this->response;
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'tag'=>$this->buildData()
		));

		return $this->response;
	}

	public function update($id)
	{
		$this->Tag = Tag::findFirst($id);

		$this->Tag->setName($this->request->getPut('name'));

		if ($this->Tag->save() == false) {
			echo "Fail: \n";
			foreach ($this->Tag->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'tag'=>$this->buildData()
		));

		return $this->response;
	}

	public function create()
	{
		$this->Tag = new Tag();

		$this->Tag->setName($this->request->getPost('name'));

		if ($this->Tag->save() == false) {
			echo "Fail: \n";
			foreach ($this->Tag->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$this->response->setStatusCode(201, "Created");
		$this->response->setJsonContent(array(
			'status'=>'OK',
			'tag'=>$this->buildData()
		));

		return $this->response;
	}

	public function createTagChtsht($chtshtId)
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		if (!$this->Tag = Tag::findFirst(['name = :name:','bind'=>['name'=>$this->request->getPost('name')]])) {
			$this->Tag = new Tag();
			$this->Tag->setName($this->request->getPost('name'));
			$this->Tag->save();
		}

		if ($this->TagChtsht = TagChtsht::findFirst(['tag_id = :tag_id: and chtsht_id = :chtsht_id:','bind'=>['tag_id'=>$this->Tag->getId(),'chtsht_id'=>$chtshtId]])) {
			$this->response->setJsonContent(array('msg'=>'This Chtsht already has this tag'));
			return $this->response;
		}

		$this->TagChtsht = new TagChtsht();

		$this->TagChtsht->setTagId($this->Tag->getId());
		$this->TagChtsht->setChtshtId($chtshtId);

		if ($this->TagChtsht->save() == false) {
			echo "Fail: \n";
			foreach ($this->TagChtsht->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'tag-add']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($chtshtId);
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'tag_id'=>$this->Tag->getId()
		)));
		$this->Versioning->save();

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'tag'=>$this->buildData()
		));

		return $this->response;
	}

	public function deleteTagChtsht($chtshtId,$tagName)
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		if (!$this->Tag = Tag::findFirst(['name = :name:','bind'=>['name'=>$tagName]])) {
			$this->response->setJsonContent(array('msg'=>'Tag does not exist'));
			return $this->response;
		}

		if (!$this->TagChtsht = TagChtsht::findFirst(['tag_id = :tag_id: and chtsht_id = :chtsht_id:','bind'=>['tag_id'=>$this->Tag->getId(),'chtsht_id'=>$chtshtId]])) {
			$this->response->setJsonContent(array('msg'=>'This Chtsht does not have this tag'));
			return $this->response;
		}

		$this->TagChtsht->delete();

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'tag-delete']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($chtshtId);
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'tag_id'=>$this->Tag->getId()
		)));
		$this->Versioning->save();

		$this->response->setJsonContent(array(
			'status'=>'OK'
		));

		return $this->response;
	}

	public function delete($id)
	{
		$vars = array();
		parse_str(file_get_contents('php://input'), $vars);

		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$vars['token']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		$this->Tag = Tag::findFirst($id);

		$this->Tag->delete();
		$this->response->setStatusCode(204, "No Content");
		$this->response->setJsonContent(array('status' => 'OK'));
		
		return $this->response;
	}

	public function buildData($obj = null)
	{
		if ($obj)
			$this->Tag = $obj;

		$chtshts = array();
		$taggedChtshts = $this->modelsManager->executeQuery("select Chtsht.id, Chtsht.name, Chtsht.url, Chtsht.description from TagChtsht c join Chtsht on c.chtsht_id = Chtsht.id where c.tag_id = {$this->Tag->getId()}");
		foreach ($taggedChtshts as $chtsht) {
			$chtshts[] = array(
				'id'=>$chtsht->id,
				'name'=>$chtsht->name,
				'url'=>$chtsht->url,
				'description'=>$chtsht->description
			);
		}

		return array(
			'id'=>$this->Tag->getId(),
			'name'=>$this->Tag->getName(),
			'chtshts'=>$chtshts
		);
	}

	public function initialize()
	{
		$this->response = new Phalcon\Http\Response();
	}
}