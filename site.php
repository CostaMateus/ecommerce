<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Address;


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
 * Rota da página que exibe os produtos no carrinho do cliente
 * @param type '/cart' 
 * @param type function( 
 * @return type
 */
$app->get('/cart', function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Cart::getMsgError()
	]);

});

/**
 * Rota que adiciona produto ao carrinho
 * @param type '/cart/:idproduct/add' 
 * @param type function($idproduct 
 * @return type
 */
$app->get('/cart/:idproduct/add', function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++)
	{
		$cart->addProduct($product);	
	}

	header("Location: /cart");
	exit;
	
});

/**
 * Rota que remove um produto do carrinho
 * @param type '/cart/:idproduct/minus' 
 * @param type function($idproduct 
 * @return type
 */
$app->get('/cart/:idproduct/minus', function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

/**
 * Rota que remove toda a qtd de um produto do carrinho
 * @param type '/cart/:idproduct/remove' 
 * @param type function($idproduct 
 * @return type
 */
$app->get('/cart/:idproduct/remove', function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

/**
 * Rota que calcula o valor do frete
 * @param type '/cart/freight' 
 * @param type function( 
 * @return type
 */
$app->post('/cart/freight', function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;

});

/**
 * Rota da página de finalização do pedido
 * @param type '/checkout' 
 * @param type function( 
 * @return type
 */
$app->get('/checkout', function(){

    User::verifyLogin(false);

	$address = new Address(); 

	$cart = Cart::getFromSession();

	if (isset($_GET['zipcode'])) 
	{
		$_GET['zipcode'] = $cart->getdeszipcode();
	}

    if (isset($_GET['zipcode']))
    {
    	$address->loadFromCEP($_GET['zipcode']);

    	$cart->setdeszipcode($_GET['zipcode']);

    	$cart->save();

    	$cart->getCalculateTotal();
    }

    if (!$address->getdesaddress()) $address->setdesaddress('');
    if (!$address->getdescomplement()) $address->setdescomplement('');
    if (!$address->getdesdistrict()) $address->setdesdistrict('');
    if (!$address->getdesstate()) $address->setdesstate('');
    if (!$address->getdescity()) $address->setdescity('');
    if (!$address->getdescountry()) $address->setdescountry('');
    if (!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl("checkout", [
		"cart"=>$cart->getValues(),
		"address"=>$address->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Address::getMsgError()
	]);
});

$app->post('/checkout', function(){

	User::verifyLogin(false);

	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '')
	{
		Cart::setMsgError("Informe o CEP.");

		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '')
	{
		Cart::setMsgError("Informe o endereço.");

		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '')
	{
		Cart::setMsgError("Informe o bairro.");

		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['descity']) || $_POST['descity'] === '')
	{
		Cart::setMsgError("Informe o cidade.");

		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desstate']) || $_POST['desstate'] === '')
	{
		Cart::setMsgError("Informe o estado (UF).");

		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['descountry']) || $_POST['descountry'] === '')
	{
		Cart::setMsgError("Informe o país.");

		header("Location: /checkout");
		exit;
	}

	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	header("Location: /order");
	exit;

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

	header("Location: /");
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
 * Rota da página de 'esqueci a senha'
 * @param type '/forgot' 
 * @param type function( 
 * @return type
 */
$app->get('/forgot' , function() {

	$page = new Page();

	$page->setTpl("forgot");

});

/**
 * Rota _POST da pagina de 'esqueci a senha', envia o email
 * @param type '/forgot' 
 * @param type function( 
 * @return type
 */
$app->post('/forgot' , function() {

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

/**
 * Rota da página que confirma o envio do email de 'esqueci a senha'
 * @param type '/forgot/sent' 
 * @param type function( 
 * @return type
 */
$app->get('/forgot/sent' , function() {

	$page = new Page();

	$page->setTpl("forgot-sent");

});

/**
 * Rota da página que reseta a senha do usuário
 * @param type '/forgot/reset' 
 * @param type function( 
 * @return type
 */
$app->get('/forgot/reset' , function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

/**
 * Rota _POST da página que reseta a senha do usuário, confirma o reset
 * @param type '/forgot/reset' 
 * @param type function( 
 * @return type
 */
$app->post('/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST['password']);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

/**
 * Rota do perfil do usuário do site
 * @param type '/profile' 
 * @param type function( 
 * @return type
 */
$app->get('/profile', function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getMsgSuccess(),
		'profileError'=>User::getMsgError()
	]);

});

/**
 * Rota _POST do perfil do usuário, alteração de dados cadastrais
 * @param type '/profile' 
 * @param type function( 
 * @return type
 */
$app->post('/profile', function(){

	User::verifyLogin();

	if (!isset($_POST['despersi]on']) || $_POST['desperson'] === '')
	{
		User::setMsgError("Preencha o seu nome.");
		header("Location: /profile");
		exit;
	}
	if (!isset($_POST['desemail']) || $_POST['desemail'] === '')
	{
		User::setMsgError("Preencha o seu e-mail.");
		header("Location: /profile");
		exit;
	}

	$user = User::getFromSession();

	if ($_POST['desemail'] !== $user->getdesemail())
	{
		if (User::checkLoginExist($_POST['desemail']) === true)
		{
			User::setMsgError("Este endereço de e-mail já está cadastrado.");
			header("Location: /profile");
			exit;
		} 
	}


	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->save();

	User::setMsgSuccess("Dados alterados com sucesso!");

	header("Location: /profile");
	exit;

});

 ?>