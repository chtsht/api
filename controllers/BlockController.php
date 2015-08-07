<?php

class BlockController extends \Phalcon\Mvc\Controller
{
	protected $Block;

	public function get($id)
	{
		$this->Block = Block::findFirst($id);

		if ($this->Block == false) {
			$this->response->setJsonContent(array('status' => 'NOT-FOUND'));
			return $this->response;
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'block'=>$this->buildData()
		));

		return $this->response;
	}

	public function update($id)
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		$this->Block = Block::findFirst($id);

		$this->Block->setName($this->request->getPut('name'));

		if ($this->Block->save() == false) {
			echo "Fail: \n";
			foreach ($this->Block->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'block-update']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($this->Block->getChtshtId());
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'name'=>$this->Block->getName()
		)));
		$this->Versioning->save();

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'block'=>$this->buildData()
		));

		return $this->response;
	}

	public function create()
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		$this->Block = new Block();

		$this->Block->setName($this->request->getPost('name'));
		$this->Block->setUserId($Session->getUserId());
		$this->Block->setChtshtId($this->request->getPost('chtsht_id'));

		if ($this->Block->save() == false) {
			echo "Fail: \n";
			foreach ($this->Block->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$last = ChtshtBlock::findFirst(array(
			'chtsht_id' => $this->request->getPost('chtsht_id'),
			'order' => 'sort_order DESC'
		));

		$sortOrder = ($last) ? $last->getSortOrder() + 1 : 1;

		$ChtshtBlock = new ChtshtBlock();
		$ChtshtBlock->setChtshtId($this->request->getPost('chtsht_id'));
		$ChtshtBlock->setBlockId($this->Block->getId());
		$ChtshtBlock->setSortOrder($sortOrder);
		$ChtshtBlock->save();

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'block-create']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($this->Block->getChtshtId());
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'id'=>$this->Block->getId(),
			'name'=>$this->Block->getName()
		)));
		$this->Versioning->save();

		$this->response->setStatusCode(201, "Created");
		$this->response->setJsonContent(array(
			'status'=>'OK',
			'block'=>$this->buildData()
		));

		return $this->response;
	}

	public function delete($id)
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}
		$Session->updateLastActive();

		$this->Block = Block::findFirst($id);

		$chtshtId = $this->Block->getChtshtId();

		$this->Block->delete();

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'block-delete']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($chtshtId);
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'id'=>$id
		)));
		$this->Versioning->save();

		$this->response->setJsonContent(array('status' => 'OK'));
		
		return $this->response;
	}

	public function sort()
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		$sortList = array();
		foreach ($this->request->getPost('order') as $x) {
			if (!empty($x)) {
				$y = explode('_',$x);
				$sortList[] = $y[1];
			}
		}

		for ($i = 0; $i < count($sortList); $i++) {
			$ChtshtBlock = ChtshtBlock::findFirst(['block_id = :block_id:','bind'=>['block_id'=>$sortList[$i]]]);
			$ChtshtBlock->setSortOrder($i);
			$ChtshtBlock->save();
		}

		$chtshtId = $this->request->getPost('chtsht_id');

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'block-sort']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($chtshtId);
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'order'=>$this->request->getPost('order')
		)));
		$this->Versioning->save();

		$this->response->setJsonContent(array(
			'status'=>'OK'
		));

		return $this->response;
	}

	public function buildData($obj = null)
	{
		if ($obj)
			$this->Block = $obj;

		$elements = array();

		$BlockElements = BlockElement::find(array(
			'block_id = :block_id:',
			'bind'=>['block_id'=>$this->Block->getId()],
			'order' => 'sort_order ASC'
		));

		foreach ($BlockElements as $BlockElement) {
			$Element = Element::findFirst($BlockElement->getElementId());

			$ElementController = new ElementController();
			$data = $ElementController->buildData($Element);

			if ($data) {
				$elements[] = $data;
			}
		}

		return array(
			'id'=>$this->Block->getId(),
			'name'=>$this->Block->getName(),
			'date_added'=>$this->Block->getDateAdded(),
			'elements'=>$elements
		);
	}

	public function initialize()
	{
		$this->response = new Phalcon\Http\Response();
	}
}