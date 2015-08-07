<?php

class UserController extends \Phalcon\Mvc\Controller
{
	protected $User;
	protected $Session;

	public function get($id)
	{
		$this->User = User::findFirst($id);

		if ($this->User == false) {
			$this->response->setJsonContent(array('status' => 'NOT-FOUND'));
			return $this->response;
		}

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'user'=>$this->buildData()
		));

		return $this->response;
	}

	public function getByToken($token)
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$token]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}
		
		$this->User = User::findFirst($Session->getUserId());

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'user'=>$this->buildData()
		));

		return $this->response;
	}

	public function update($id)
	{
		$this->User = User::findFirst($id);

		$this->User->setDisplayName($this->request->getPost('display_name'));

		$this->User->save();

		$this->response->setJsonContent(array(
			'status'=>'OK',
			'user'=>$this->buildData()
		));

		return $this->response;
	}

	public function delete($id)
	{
		$this->User = User::findFirst($id);

		$this->User->delete();
		$this->response->setStatusCode(204, "No Content");
	}

	public function buildData($obj = null)
	{
		if ($obj)
			$this->User = $obj;

		return array(
			'id'=>$this->User->getId(),
			'display_name'=>$this->User->getDisplayName(),
			'avatar'=>$this->User->getAvatar(),
			'date_added'=>$this->User->getDateAdded()
		);
	}

	public function initialize()
	{
		$this->response = new Phalcon\Http\Response();
	}
}