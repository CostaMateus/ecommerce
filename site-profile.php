<?php 

use \Hcode\Page;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Order;


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

/**
 * 
 * @param type '/profile/orders' 
 * @param type function( 
 * @return type
 */
$app->get('/profile/orders', function(){

	User::verifyLogin(false);

	$user = User::getFromSession();
	
	$page = new Page();

	$page->setTpl("profile-orders", [
		"orders"=>$user->getOrders()
	]);

});

/**
 * 
 * @param type '/profile/orders/:idorder' 
 * @param type function($idorder 
 * @return type
 */
$app->get('/profile/orders/:idorder', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = new Cart();

	$cart->get((int)$order->getidcart());

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		"order"=>$order->getValues(),
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts()
	]);

});

$app->get('/profile/change-password', function(){

	User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		"changePassError"=>User::getMsgError(),
		"changePassSuccess"=>User::getMsgSuccess()
	]);

});


$app->post("/profile/change-password", function(){

	User::verifyLogin(false);

	if (isser($_POST['current_pass']) || $_POST['current_pass'] === '')
	{
		User::setMsgError("Digite a senha atual!");
		header("Location: /profile/change-password");
		exit;
	}

	if (isser($_POST['new_pass']) || $_POST['new_pass'] === '')
	{
		User::setMsgError("Digite a nova senha!");
		header("Location: /profile/change-password");
		exit;
	}

	if (isser($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '')
	{
		User::setMsgError("Confirme a nova senha!");
		header("Location: /profile/change-password");
		exit;
	}

	if ($_POST['current_pass'] === $_POST['new_pass'])
	{
		User::setMsgError("A sua nova senha deve ser diferente da atual!");
		header("Location: /profile/change-password");
		exit;	
	}

	$user = User::getFromSession();

	if (!password_verify($_POST['current_pass'], $user->getdespassword()))
	{
		User::setMsgError("Senha atual inválida!");
		header("Location: /profile/change-password");
		exit;
	}

	$user->setdespassword($_POST['new_pass']);

	$user->update();

	User::setMsgSuccess("Senha alterada com sucesso!");
	header("Location: /profile/change-password");
	exit;

});
 ?>