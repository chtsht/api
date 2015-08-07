<?php

use GuzzleHttp\Client as HttpClient;

class AuthController extends \Phalcon\Mvc\Controller
{
	public function github()
	{
		$Client = new HttpClient();
		$headers['Accept'] = 'application/json';
		$body = array(
			'client_id' => $this->config->github->id,
			'client_secret' => $this->config->github->secret,
			'state' => $this->request->getPost('state'),
			'code' => $this->request->getPost('code')
		);

		$Response = $Client->post('https://github.com/login/oauth/access_token', ['headers'=>$headers,'body'=>$body]);

		$resp = json_decode($Response->getBody(), true);
		if (!isset($resp['access_token'])) {
			$this->response->setJsonContent(array(
				'status'=>'FAIL',
				'resp'=>$resp
			));

			return $this->response;
		}
		$accessToken = $resp['access_token'];

		$this->UserAuth = UserAuth::findFirst(['access_token = :access_token:','bind'=>['access_token'=>$accessToken]]);

		if ($this->UserAuth) {
			$this->User = User::findFirst($this->UserAuth->getUserId());
			if (!$token = $this->createSession()) {
				echo "Fail: \n";
				foreach ($this->Session->getMessages() as $message) {
					echo $message, "\n";
				}

				return $this->response;
			}
		} else {
			$Response = $Client->get('https://api.github.com/user?access_token='.$accessToken);

			$resp = json_decode($Response->getBody(), true);
			if (!$token = $this->createUser($resp,$accessToken)) {
				echo "Fail: \n";
				foreach ($this->Session->getMessages() as $message) {
					echo $message, "\n";
				}

				return $this->response;
			}
		}

		$this->response->setStatusCode(201, "Created");
		$this->response->setJsonContent(array(
			'status'=>'OK',
			'token'=>$token
		));
		return $this->response;
	}

	public function createUser($resp,$accessToken)
	{
		$this->User = new User();

		$this->User->setDisplayName($resp['login']);
		if ($resp['avatar_url'])
			$this->User->setAvatar($resp['avatar_url']);
		$this->User->setStatus(1);

		if ($this->User->save() == false) {
			echo "Fail: \n";
			foreach ($this->User->getMessages() as $message) {
				echo $message, "\n";
			}

			return $this->response;
		}

		$this->UserAuth = new UserAuth();
		$this->UserAuth->setUserId($this->User->getId());
		$this->UserAuth->setIdentity($resp['login']);
		$this->UserAuth->setAccessToken($accessToken);
		$this->UserAuth->save();

		if (!$token = $this->createSession()) {
			return false;
		}
		return $token;
	}

	public function logout()
	{
		if (!$Session = Session::findFirst(['token = :token:','bind'=>['token'=>$_SERVER['HTTP_TOKEN']]])) {
			$this->response->setJsonContent(array('msg'=>'Invalid User'));
			return $this->response;
		}

		$Session->delete();

		$this->response->setJsonContent(array(
			'status'=>'OK'
		));

		return $this->response;
	}

	private function createSession()
	{
		$token = $this->generateToken();

		$this->Session = new Session();
		$this->Session->setUserId($this->User->getId());
		$this->Session->setToken($token);

		if ($this->Session->save() == false) {
			echo "Fail: \n";
			foreach ($this->User->getMessages() as $message) {
				echo $message, "\n";
			}

			return false;
		}

		return $token;
	}

	private function generateToken()
	{
		$token = sha1($this->User->getId() + time() + rand(1,10000));
		return $token;
	}

	public function initialize()
	{
		$this->response = new Phalcon\Http\Response();
	}
}