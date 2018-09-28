<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {

	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
	
});

$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;
	
});

$app->get("/admin/users", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users");

});

$app->get("/admin/users/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users");

});

$app->get("/admin/users/:iduser", function($iduser){ //recebe o id para carregar o formulario do usuario ja preenchido

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users");

});

$app->post("/admin/users/create", function() {//rota para salvar

		User::verifyLogin();

});

$app->post("/admin/users/:iduser", function() {//rota para salvar

		User::verifyLogin();

});

$app->delete("/admin/users/iduser", function() {//rota para salvar

		User::verifyLogin();

});


$app->run();

 ?>