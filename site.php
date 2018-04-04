<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\User;
use \Hcode\Model\Order;


/**
 * Rota padrão da página inicial do site
 * @param type '/' 
 * @param type function( 
 * @return type
 */
$app->get('/', function() {
    
    $products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'=>$products
	]);

});

/**
 * Rota da página que lista produtos de uma categoria no site 
 * @param type '/categories/:idcategory' 
 * @param type function($idcategory 
 * @return type
 */
$app->get('/categories/:idcategory', function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	
	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = array();

	for ($i = 1; $i <= $pagination['pages']; $i++)
	{
		array_push ($pages, array(
			'link'=>'/categories/' . $category->getidcategory() . '?page=' . $i,
			'page'=>$i
		));
	}

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages
	]);
});

/**
 * Rota da página que exibe os detalhes de um produto
 * @param type '/products/:desurl' 
 * @param type function($desurl 
 * @return type
 */
$app->get('/products/:desurl', function($desurl){
	
	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl('product-detail', [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);
});

/**
 * Rota da página de login de usuário no site
 * @param type '/login' 
 * @param type function( 
 * @return type
 */
$app->get('/login', function(){

	$page = new Page();

	$page->setTpl("login", [
		"error"=>User::getMsgError(),
		"errorRegister"=>User::getErrorRegister(),
		"registerValues"=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ["name"=>"", "email"=>"", "phone"=>""]
	]);

});

/**
 * Rota _POST da página de login de usuário no site
 * @param type '/login' 
 * @param type function( 
 * @return type
 */
$app->post('/login', function(){

	try {
	
		User::login($_POST['login'], $_POST['password']);

		
	
	} catch (Exception $e) {

		User::setMsgError($e->getMessage());

	}

	header("Location: /checkout");
	exit;

});

/**
 * Rota que efetua o logout de um usuário
 * @param type '/logout' 
 * @param type function( 
 * @return type
 */
$app->get('/logout', function(){
	
	User::logout();

	header("Location: /login");
	exit;

});

/**
 * 
 * @param type '/register' 
 * @param type function( 
 * @return type
 */
$app->post('/register', function(){

	$_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['name']) || $_POST['name'] == '')
	{
		User::setErrorRegister("Insira o seu nome.");

		header("Location: /login");
		exit;
	}

	if (!isset($_POST['email']) || $_POST['email'] == '')
	{
		User::setErrorRegister("Insira um e-mail válido.");

		header("Location: /login");
		exit;
	}

	if (!isset($_POST['password']) || $_POST['password'] == '')
	{
		User::setErrorRegister("Insira uma senha.");

		header("Location: /login");
		exit;
	}

	if (User::checkLoginExist($_POST['email']) === true)
	{
		User::setErrorRegister("Este e-mail já está em uso!");

		header("Location: /login");
		exit;
	}
	
	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'], 
		'desperson'=>$_POST['name'], 
		'desemail'=>$_POST['email'], 
		'despassword'=>$_POST['password'], 
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header("Location: /checkout");
	exit;

});

/**
 * Rota da página de finalização do pedido
 * @param type '/order/:idorder' 
 * @param type function($idorder 
 * @return type
 */
$app->get('/order/:idorder', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		"order"=>$order->getValues()
	]);
});



 ?>