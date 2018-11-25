<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){

	User::verifyLogin();

	$products = Product::ListAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		'products'=>$products
	]);

});

$app->get("/admin/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");

});

$app->post("/admin/products/create", function(){

	User::verifyLogin();//Autenticar login

	$product = new Product();//Carregar Pagina Produtos

	$product->setData($_POST);//Receber Produtos via POST

	$product->save();//Salvar Alteracoes
	
	header("Location: /admin/products");//Template
	exit;
});

$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();//Autenticar login

	$product = new Product();//Carregar Pagina Produtos

	$product->get((int)$idproduct);

	$page = new PageAdmin();//Carregar Pagina Admin

	$page->setTpl("products-update", [//Template

		'product'=>$product->getValues()
	]);

});
//Route: Photo products
$app->post("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();//Autenticar login
	
	$product = new Product();//Carregar Pagina Produtos
	
	$product->get((int)$idproduct);//Carregar Pagina Produtos
	
	$product->setData($_POST);//Receber Produtos via POST
	
	$product->save();//Salvar Alteracoes
	
	$product->setPhoto($_FILES["file"]);

	header('Location: /admin/products');//Template
	exit;

});

$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();//Autenticar login

	$product = new Product();//Carregar Pagina Produtos

	$product->get((int)$idproduct);

	$product->delete();//Deletar Produtos

	header(('Location: /admin/products'));//Template
	exit;
	
});

?>

