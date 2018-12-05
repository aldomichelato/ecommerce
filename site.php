<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

$app->get('/', function() {

	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

$app->get("/categories/:idcategory", function($idcategory){
	/* 
	 * Em qual página estamos para chamarmos a correta;
	 * Verificar se foi passado o dado na URL, porém
	 * no primeiro acesso não teremos o número.
	 * Se nada fora passado será a página 1;
	 */
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	$category = new Category();
	$category->get((int)$idcategory);
	$pagination = $category->getProductsPage($page);
	$pages = [];
	/*
	 * Enquanto $i for menor ou igual a $pagination['pages'] faca
	 * array_push
	 * 'link': Para qual caminho será encaminhado o usuário;
	 * /categories/;
	 * idcategory:$category->getidcategory();
	 * ?page=: recebendo o dado via GET conforme acima;
	 * $i: Para qual página.
	 */
	for ($i=1; $i <= $pagination['pages']; $i++) { 
    array_push($pages, [
        'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
	'page'=>$i
    ]);
	}
/* 
 * Enquanto $i for menor ou igual a $pagination['pages'] faca
 * array_push
 * 'link': Para qual caminho será encaminhado o usuário;
 * /categories/;
 * idcategory: $category->getidcategory();
 * ?page=: recebendo o dado via GET conforme acima;
 * $i: Para qual página.
 */
	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);
});


?>