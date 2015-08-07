<?php

class ElementController extends \Phalcon\Mvc\Controller
{
	protected $Element;

	public function get($id)
	{
		$this->Element = Element::findFirst($id);

		if ($this->Element == false) {
			$this->response->setJsonContent(array('status' => 'NOT-FOUND'));
			return $this->response;
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'element'=>$this->buildData()
		));

		return $this->response;
	}

	public function update($id)
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		if ($this->request->getPut('type') and !$ElementType = ElementType::findFirst(['type = :type:','bind'=>['type'=>$this->request->getPut('type')]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid Type'));
			return $this->response;
		}

		$this->Element = Element::findFirst($id);

		// If there are changes, save
		if ($this->Element->getContent() != $this->cleanElement($this->request->getPut('content')) or $this->Element->getDescription() != $this->cleanElement($this->request->getPut('description'))) {
			$this->Element->setContent($this->cleanElement($this->request->getPut('content')));
			$this->Element->setDescription($this->cleanElement($this->request->getPut('description')));
			if ($ElementType)
				$this->Element->setTypeId($ElementType->getId());

			if ($this->Element->save() == false) {
				echo "Fail: \n";
				foreach ($this->Element->getMessages() as $message) {
					echo $message, "\n";
				}

				return $this->response;
			}

			$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'element-update']]);
			$this->Versioning = new Versioning();
			$this->Versioning->setUserId($Session->getUserId());
			$this->Versioning->setChtshtId($this->Element->getChtshtId());
			$this->Versioning->setModuleId($Module->getId());
			$this->Versioning->setData(json_encode(array(
				'id'=>$this->Element->getId(),
				'content'=>$this->Element->getContent(),
				'description'=>$this->Element->getDescription(),
			)));
			$this->Versioning->save();
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'element'=>$this->buildData()
		));

		return $this->response;
	}

	public function create()
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		if (!$ElementType = ElementType::findFirst(['type = :type:','bind'=>['type'=>$this->request->getPost('type')]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid Type'));
			return $this->response;
		}

		$this->Element = new Element();

		$this->Element->setContent($this->cleanElement($this->request->getPost('content')));
		$this->Element->setDescription($this->cleanElement($this->request->getPost('description')));
		$this->Element->setBlockId($this->request->getPost('block_id'));
		$this->Element->setTypeId($ElementType->getId());
		$this->Element->setUserId($Session->getUserId());

		if ($this->Element->save() == false) {
			echo "Fail: \n";
			foreach ($this->Element->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$last = BlockElement::findFirst(array(
			'block_id' => $this->request->getPost('block_id'),
			'order' => 'sort_order DESC'
		));

		$sortOrder = ($last) ? $last->getSortOrder() + 1 : 1;

		$BlockElement = new BlockElement();
		$BlockElement->setBlockId($this->request->getPost('block_id'));
		$BlockElement->setElementId($this->Element->getId());
		$BlockElement->setSortOrder($sortOrder);
		$BlockElement->save();

		if ($this->Element->save() == false) {
			echo "Fail: \n";
			foreach ($this->Element->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'element-create']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($this->Element->getChtshtId());
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'id'=>$this->Element->getId(),
			'blockId'=>$this->Element->getBlockId(),
			'typeId'=>$this->Element->getTypeId(),
			'content'=>$this->Element->getContent(),
			'description'=>$this->Element->getDescription(),
		)));
		$this->Versioning->save();

		$this->response->setStatusCode(201, "Created");
		$this->response->setJsonContent(array(
			'status'=>'OK',
			'element'=>$this->buildData()
		));

		return $this->response;
	}

	public function delete($id)
	{
		$vars = array();
		parse_str(file_get_contents('php://input'), $vars);

		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		$this->Element = Element::findFirst($id);

		if (!$this->Element) {
			$this->response->setJsonContent(array('status' => 'FAIL'));

			return $this->response;

		}

		// Get chtshtId before element is removed
		$chtshtId = $this->Element->getChtshtId();

		$this->Element->delete();

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'element-delete']]);
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
			$BlockElement = BlockElement::findFirst(['element_id = :element_id:','bind'=>['element_id'=>$sortList[$i]]]);
			$BlockElement->setSortOrder($i);
			$BlockElement->save();
		}

		$this->Block = Block::findFirst($this->request->getPost('block_id'));

		$chtshtId = $this->Block->getChtshtId();

		$Module = Module::findFirst(['module = :module:','bind'=>['module'=>'element-sort']]);
		$this->Versioning = new Versioning();
		$this->Versioning->setUserId($Session->getUserId());
		$this->Versioning->setChtshtId($chtshtId);
		$this->Versioning->setModuleId($Module->getId());
		$this->Versioning->setData(json_encode(array(
			'block_id'=>$this->request->getPost('block_id'),
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
			$this->Element = $obj;

		$ElementType = ElementType::findFirst($this->Element->getTypeId());

		return array(
			'id'=>$this->Element->getId(),
			'type'=>$ElementType->getType(),
			'date_added'=>$this->Element->getDateAdded(),
			'content'=>$this->Element->getContent(),
			'description'=>$this->Element->getDescription()
		);
	}

	private function cleanElement($element)
	{
		$element = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $element);
		$element = htmlentities($element);
		$element = trim($element);
		return $element;
	}

	public function initialize()
	{
		$this->response = new Phalcon\Http\Response();
	}
}