<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

//::::::::::::Route URL
$app->get("/products/:desurl", function($desurl){
	$product = new Product();
	$product->getFromURL($desurl);
	$page = new Page;
	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		/* 
		 * Mostrar a qual categoria o produto pertence. 
		 * Template usuario/categorias
		 */
		'categories'=>$product->getCategories()
	]);
});


$app->get("/cart", function() {
    $cart = Cart::getFromSession();
    $page = new Page();
    $page->setTpl("cart", [
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts(),
        'error' => $cart->getCartError()
    ]);
});

$app->get("/cart/:idproduct/add", function($idproduct) {

    $product = new Product();
    $product->get((int) $idproduct);
    $cart = Cart::getFromSession();
    $qtd = (isset($_GET['qtd'])) ? (int) $_GET['qtd'] : 1;
    for ($i = 0; $i < $qtd; $i++) {
        $cart->addProduct($product);
    }
    header("Location: /cart");
    exit;

});

$app->get("/cart/:idproduct/minus", function($idproduct) {

    $product = new Product();
    $product->get((int) $idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product);
    header("Location: /cart");
    exit;

});

$app->get("/cart/:idproduct/remove", function($idproduct) {

    $product = new Product();
    $product->get((int) $idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);
    header("Location: /cart");
    exit;

});

$app->post("/cart/freight", function() {

    $nrzipcode = str_replace("-", "", $_POST['zipcode']);
    $cart = Cart::getFromSession();
    $cart->setFreight($nrzipcode);
    header("Location: /cart");
    exit;

});

$app->get("/checkout", function() {

    User::verifyLogin(false);

    $address = new Address();

    $cart = Cart::getFromSession();

    $page = new Page();

    $page->setTpl("checkout", [
        'cart' => $cart->getValues(),
        'address' => $address->getValues()
    ]);

});

$app->get("/login", function() {
    $page = new Page();
    $page->setTpl("login", [
        'loginError' => User::getError(),
        'errorRegister' => User::getErrorRegister(),
        'registerValues' => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] :
                ['name' => '', 'email' => '', 'phone' => '']
    ]);
});

$app->post("/login", function() {
    try {
        User::login($_POST["login"], $_POST["password"]);
    } catch (Exception $e) {
        User::setError($e->getMessage());
    }
    header("Location: /checkout");
    exit;
});

$app->get("/logout", function() {
    User::logout();
    header("Location: /login");
    exit;
});

$app->post("/register", function() {

    $_SESSION['registerValues'] = $_POST;

    if (!isset($_POST['name']) || $_POST['name'] == '') {
        User::setErrorRegister("Preencha o seu nome!");
        header("Location: /login");
        exit;
    }
    if (!isset($_POST['email']) || $_POST['email'] == '') {
        User::setErrorRegister("Preencha o seu e-mail!");
        header("Location: /login");
        exit;
    }
    if (!isset($_POST['password']) || $_POST['password'] == '') {
        User::setErrorRegister("Preencha a sua senha!");
        header("Location: /login");
        exit;
    }
    if (User::checkLoginExist($_POST['email']) === true) {
        User::setErrorRegister("Endereço de e-mail já registrado!");
        header("Location: /login");
        exit;
    }
    $user = new User();
    $user->setData([
        'inadmin' => 0,
        'deslogin' => $_POST['email'],
        'desperson' => $_POST['name'],
        'desemail' => $_POST['email'],
        'despassword' => $_POST['password'],
        'nrphone' => $_POST['phone']
    ]);
    $user->save();
    User::login($_POST['email'], $_POST['password']);
    header('Location: /checkout');
   // unset($_SESSION['registerValues']);
    exit;
});

$app->get("/forgot", function() {
    $page = new Page();
    $page->setTpl("forgot");
});

$app->post("/forgot", function() {
    $user = User::getForgot($_POST["email"], false); //passa false pelo fato de nao ser admin
    header("Location: /forgot/sent");
    exit;
});

$app->get("/forgot/sent", function() {
    $page = new Page();
    $page->setTpl("forgot-sent");
});

$app->get("/forgot/reset", function() {
    $user = User::validForgotDecrypt($_GET["code"]);
    $page = new Page();
    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));
});

$app->post("/forgot/reset", function() {
    $forgot = User::validForgotDecrypt($_POST["code"]);
    User::setForgotUsed($forgot["idrecovery"]);
    $user = new User();
    $user->get((int) $forgot["iduser"]);
    $password = User::getPasswordHash($_POST['password']);
    $user->setPassword($password);
    $page = new Page();
    $page->setTpl("forgot-reset-success");

});




$app->get("/profile", function() {
    
    User::verifyLogin(false);

    $user = User::getFromSession();
    
    $page = new Page();
    
    $page->setTpl("profile", [
        'user' => $user->getValues(),
        'profileMsg' => User::getSuccess(),
        'profileError' => User::getError()
    
    ]);

});



$app->post("/profile", function() {
    User::verifyLogin(false);
    if (!isset($_POST['desperson']) || $_POST['desperson'] == '') {
        User::setError("Preencha o seu nome.");
        header("Location: /profile");
        exit;
    }
    if (!isset($_POST['desemail']) || $_POST['desemail'] == '') {
        User::setError("Preencha o seu endereço de e-mail.");
        header("Location: /profile");
        exit;
    }
    $user = User::getFromSession();
    if ($_POST['desemail'] != $user->getdesemail()) {
        if (User::checkLoginExist($_POST['desemail']) === true) {
            User::setError("Este endereço de e-mail já está cadastrado.");
            header("Location: /profile");
            exit;
        }
    }
    $_POST['inadmin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];
    $user->setData($_POST);
    $user->update();
    User::setSuccess("Dados alterados com sucesso.");
    header("Location: /profile");
    exit;
});


?>