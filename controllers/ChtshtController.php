<?php

class ChtshtController extends \Phalcon\Mvc\Controller
{
	protected $Chtsht;

	public function get($id)
	{
		if ($Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$Session->updateLastActive();
		}

		$this->Chtsht = Chtsht::findFirst($id);

		if ($this->Chtsht == false) {
			$this->response->setJsonContent(array('status' => 'NOT-FOUND'));
			return $this->response;
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'chtsht'=>$this->buildData()
		));

		return $this->response;
	}

	public function getAll()
	{
		$Chtshts = Chtsht::find(array(
			"order" => "name ASC"
		));

		$chtshts = array();
		foreach ($Chtshts as $Chtsht) {
			$chtshts[] = array(
				'id'=>$Chtsht->getId(),
				'name'=>$Chtsht->getName(),
				'url'=>$Chtsht->getUrl(),
				'description'=>$Chtsht->getDescription(),
				'date_added'=>$Chtsht->getDateAdded()
			);
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'chtshts'=>$chtshts
		));

		return $this->response;
	}

	public function getByUrl($url)
	{
		if ($Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$Session->updateLastActive();
		}

		if (!$this->Chtsht = Chtsht::findFirst(['url = :url:','bind'=>['url'=>$url]])) {
			$this->response->setJsonContent(array('status' => 'NOT-FOUND'));
			return $this->response;
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'chtsht'=>$this->buildData()
		));

		return $this->response;
	}

	public function latest($limit)
	{
		$limit = ($limit) ? $limit : 10;
		$Chtshts = Chtsht::find(array(
			"order" => "date_added DESC",
			"limit" => $limit
		));

		$chtshts = array();
		foreach ($Chtshts as $Chtsht){
			$chtshts[] = $this->buildData($Chtsht);
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'chtshts'=>$chtshts
		));

		return $this->response;
	}

	public function search()
	{
		$search = array();

		if ($this->request->getPost('name'))
			$search[] = 'name LIKE "%'.$this->request->getPost('name').'%"';

		$search['limit'] = ($this->request->getPost('limit')) ? $this->request->getPost('limit') : '20';

		$Chtshts = Chtsht::find($search);

		$chtshts = array();
		foreach ($Chtshts as $Chtsht){
			$chtshts[] = array(
				'id'=>$Chtsht->getId(),
				'name'=>$Chtsht->getName(),
				'url'=>$Chtsht->getUrl(),
				'description'=>$Chtsht->getDescription(),
				'user_id'=>$Chtsht->getUserId(),
				'date_added'=>$Chtsht->getDateAdded()
			);
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'chtshts'=>$chtshts
		));

		return $this->response;
	}

	public function update($id)
	{
		if (!isset($_SERVER['HTTP_TOKEN']) or !$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}
		$Session->updateLastActive();

		$this->Chtsht = Chtsht::findFirst($id);

		// If there are changes, save
		if ($this->Chtsht->getName() != $this->request->getPut('name') or $this->Chtsht->getDescription() != $this->cleanDescription($this->request->getPut('description'))) {
			$this->Chtsht->setName($this->request->getPut('name'));
			$this->Chtsht->setDescription($this->cleanDescription($this->request->getPut('description')));

			$this->Chtsht->save();

			$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'chtsht-update']]);
			$this->Versioning = new Versioning();
			$this->Versioning->setUserId($Session->getUserId());
			$this->Versioning->setChtshtId($this->Chtsht->getId());
			$this->Versioning->setModuleId($Module->getId());
			$this->Versioning->setData(json_encode(array(
				'name'=>$this->Chtsht->getName(),
				'description'=>$this->Chtsht->getDescription()
			)));
			$this->Versioning->save();
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'chtsht'=>$this->buildData()
		));

		return $this->response;
	}

	public function create()
	{
		if (!isset($_SERVER['HTTP_TOKEN']) or !$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}
		$Session->updateLastActive();

		$this->Chtsht = new Chtsht();

		$this->Chtsht->setName($this->request->getPost('name'));
		$this->Chtsht->setDescription($this->request->getPost('description'));
		$this->Chtsht->setUrl($this->generateUrl());
		$this->Chtsht->setUserId($Session->getUserId());

		if ($this->Chtsht->save() == false) {
			$messages = array();
			foreach($this->Chtsht->getMessages() as $msg) {
				$messages[] = $msg->getMessage();
			}

			$this->response->setStatusCode(201, "Created");
			$this->response->setJsonContent(array(
				'status'=>'FAILURE',
				'msg'=>$messages
			));

			return $this->response;
		}

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'chtsht-create']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($this->Chtsht->getId());
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'name'=>$this->Chtsht->getName(),
			'description'=>$this->Chtsht->getDescription(),
			'url'=>$this->Chtsht->getUrl(),
			'userId'=>$this->Chtsht->getUserId()
		)));
		$this->Versioning->save();

		$this->response->setStatusCode(201, "Created");
		$this->response->setJsonContent(array(
			'status'=>'OK',
			'chtsht'=>$this->buildData()
		));

		return $this->response;
	}

	public function delete($id)
	{
		if (!isset($_SERVER['HTTP_TOKEN']) or !$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}
		$Session->updateLastActive();

		$this->Chtsht = Chtsht::findFirst($id);

		$this->Chtsht->delete();

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'chtsht-delete']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($this->Chtsht->getId());
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->save();

		$this->response->setStatusCode(204, "No Content");
		$this->response->setJsonContent(array('status' => 'OK'));
		
		return $this->response;
	}

	public function history($id)
	{
		$query = $this->modelsManager->createQuery('SELECT
					v.id as id,
					v.user_id as user_id,
					v.data as data,
					v.date_added as date_added,
					Module.module,
					User.display_name,
					User.avatar
				FROM Versioning AS v
				JOIN Module ON v.module_id = Module.id
				JOIN User ON v.user_id = User.id
				WHERE v.chtsht_id = :chtsht_id:
				ORDER BY v.date_added DESC');

		$Versionings = $query->execute(array('chtsht_id'=>$id));

		$versionings = array();
		foreach ($Versionings as $Versioning) {
			$versionings[] = array(
				'id'=>$Versioning->id,
				'user_id'=>$Versioning->user_id,
				'display_name'=>$Versioning->display_name,
				'avatar'=>$Versioning->avatar,
				'module'=>$Versioning->module,
				'date_added'=>$Versioning->date_added,
				'data'=>$Versioning->data
			);
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'history'=>$versionings
		));

		return $this->response;
	}

	private function generateUrl($inc = 0)
	{
		$incStr = ($inc === 0) ? '' : '-' . $inc;
		$url = preg_replace('/ |\/|\./s', '-', preg_replace('~\s{2,}~', ' ', preg_replace('/[^a-zA-Z0-9_ \.\/]/s', '', $this->request->getPost('name')))) . $incStr;

		$inc++;

		if (Chtsht::findFirst(['url = :url:','bind'=>['url'=>$url]]))
			return $this->generateUrl($inc);

		return strtolower($url);
	}

	public function buildData($obj = null)
	{
		if ($obj)
			$this->Chtsht = $obj;

		$blocks = array();
		$tags = array();

		$ChtshtBlocks = ChtshtBlock::find(array(
			'chtsht_id = :chtsht_id:',
			'bind'=>['chtsht_id'=>$this->Chtsht->getId()],
			'order' => 'sort_order ASC'
		));

		foreach ($ChtshtBlocks as $ChtshtBlock) {
			$Block = Block::findFirst($ChtshtBlock->getBlockId());

			$BlockController = new BlockController($Block);
			$data = $BlockController->buildData($Block);

			if ($data) {
				$blocks[] = $data;
			}
		}

		$TagChtshts = TagChtsht::find(array(
			'chtsht_id = :chtsht_id:',
			'bind'=>['chtsht_id'=>$this->Chtsht->getId()]
		));

		foreach ($TagChtshts as $TagChtsht) {
			$Tag = Tag::findFirst($TagChtsht->getTagId());

			if ($Tag) {
				$tags[] = $Tag->getName();
			}
		}

		return array(
			'id'=>$this->Chtsht->getId(),
			'name'=>$this->Chtsht->getName(),
			'url'=>$this->Chtsht->getUrl(),
			'description'=>$this->Chtsht->getDescription(),
			'user_id'=>$this->Chtsht->getUserId(),
			'date_added'=>$this->Chtsht->getDateAdded(),
			'blocks'=>$blocks,
			'tags'=>$tags
		);
	}

	private function cleanDescription($description)
	{
		$description = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $description);
		$description = htmlentities($description);
		$description = trim($description);
		return $description;
	}

	public function initialize()
	{
		$this->response = new Phalcon\Http\Response();
	}
}