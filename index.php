<?php

use Phalcon\Config\Adapter\Ini as ConfigIni;

$config = new ConfigIni('config/config.ini');
require 'config/loader.php';

require_once 'vendor/autoload.php';

$di = new \Phalcon\DI\FactoryDefault();

//Set up the database service
$di->set('db', function() use ($config){
	$conn = array(
		"host" => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname" => $config->database->name
	);
	return new \Phalcon\Db\Adapter\Pdo\Mysql($conn);
});

$di->set('config',$config);

$app = new Phalcon\Mvc\Micro($di);
$app->get('/', function() {
	header('Location: http://www.chtsht.io/');
});

$ChtshtCollection = new Phalcon\Mvc\Micro\Collection();
$ChtshtCollection->setHandler(new ChtshtController());
$ChtshtCollection->get('/chtshts','getAll');
$ChtshtCollection->get('/chtsht/{url:[a-zA-Z0-9_-]+}','getByUrl');
$ChtshtCollection->get('/chtsht/{id:[0-9]+}','get');
$ChtshtCollection->post('/chtsht','create');
$ChtshtCollection->put('/chtsht/{id:[0-9]+}','update');
$ChtshtCollection->delete('/chtsht/{id:[0-9]+}','delete');

$ChtshtCollection->post('/chtsht/search','search');
$ChtshtCollection->get('/chtsht/latest[/]?{amount:[0-9]*}','latest');
$ChtshtCollection->get('/chtsht/history/{id:[0-9]+}','history');
$app->mount($ChtshtCollection);

$BlockCollection = new Phalcon\Mvc\Micro\Collection();
$BlockCollection->setHandler(new BlockController());
$BlockCollection->get('/block/{id:[0-9]+}','get');
$BlockCollection->post('/block','create');
$BlockCollection->put('/block/{id:[0-9]+}/order','order');
$BlockCollection->put('/block/{id:[0-9]+}','update');
$BlockCollection->delete('/block/{id:[0-9]+}','delete');

$BlockCollection->post('/block/sort','sort');
$app->mount($BlockCollection);

$ElementCollection = new Phalcon\Mvc\Micro\Collection();
$ElementCollection->setHandler(new ElementController());
$ElementCollection->get('/element/{id:[0-9]+}','get');
$ElementCollection->post('/element','create');
$ElementCollection->put('/element/{id:[0-9]+}','update');
$ElementCollection->delete('/element/{id:[0-9]+}','delete');

$ElementCollection->post('/element/sort','sort');
$app->mount($ElementCollection);

$TagCollection = new Phalcon\Mvc\Micro\Collection();
$TagCollection->setHandler(new TagController());
$TagCollection->get('/tags','getAll');
$TagCollection->get('/tag/{id:[0-9]+}','get');
$TagCollection->get('/tag/{name:[a-zA-Z0-9_.-]+}','getByName');
$TagCollection->post('/tag','create');
$TagCollection->put('/tag/{id:[0-9]+}','update');
$TagCollection->delete('/tag/{id:[0-9]+}','delete');

$TagCollection->post('/tag-chtsht/{chtshtId:[0-9]+}','createTagChtsht');
$TagCollection->delete('/tag-chtsht/{chtshtId:[0-9]+}/{tagName:[a-zA-Z0-9_.-]+}','deleteTagChtsht');
$app->mount($TagCollection);

$UserCollection = new Phalcon\Mvc\Micro\Collection();
$UserCollection->setHandler(new UserController());
$UserCollection->get('/user/{token:[a-zA-Z0-9_-]+}','getByToken');
$UserCollection->get('/user/{id:[0-9]+}','get');
$UserCollection->post('/user','create');
$UserCollection->put('/user/{id:[0-9]+}','update');
$UserCollection->delete('/user/{id:[0-9]+}','delete');
$app->mount($UserCollection);

$AuthCollection = new Phalcon\Mvc\Micro\Collection();
$AuthCollection->setHandler(new AuthController());
$AuthCollection->post('/login/github','github');
$AuthCollection->post('/logout','logout');
$app->mount($AuthCollection);

$app->notFound(function () use ($app) {
	$app->response->setStatusCode(404, "Not Found")->sendHeaders();
	echo 'Page not found.';
});

$app->handle();