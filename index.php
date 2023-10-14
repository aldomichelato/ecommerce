<?php 
session_start(); //sessão rodando no php
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
    
    User::VerifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function() {
    
	$page = new PageAdmin([ //na pagina de login, é necessário verificar se aaparece o cabeçalho e o rodapé, pois geralmente nesse caso NAO DEVEM APARECER
		"header"=>false, //desabilitar o header padrão
		"footer"=>false  //e também o footer padrão
	]);

	$page->setTpl("login"); //chama o template login

});

$app->post('/admin/login', function(){  //rota post
    
	User::login($_POST["login"], $_POST["password"]);  //metodo estatico chamado login que recebe o post de login e password do formulario
	
	header("Location: /admin"); //redireciona para a administração
	exit;

});

$app->get('/admin/logout', function() { //rota de logout

	User::logout(); //chama a sessão logout

	header("Location: /admin/login"); // chama a tela de login
	exit;

});

$app->get('/admin/users', function() {

	 User::VerifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users");

});

$app->get('/admin/users/create', function() {

	 User::VerifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});

$app->get('/admin/user/:iduser', function($iduser) {  //o valor que vem no :user é o que é recebido na função

	 User::VerifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-update");

});

$app->post('/admin/users/create', function() {  //o valor que vem no :user é o que é recebido na função

	 User::VerifyLogin();

});

$app->post('/admin/user/:iduser', function($iduser) {  //o valor que vem no :user é o que é recebido na função

	 User::VerifyLogin();

});

$app->delete('/admin/user/:iduser', function($iduser) {  //o valor que vem no :user é o que é recebido na função

	 User::VerifyLogin();

});

$app->run();

 ?>