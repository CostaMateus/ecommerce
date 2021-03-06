<?php

use \Hcode\Page;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Address;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


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
    if (!$address->getdesnumber()) $address->setdesnumber('');
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

/**
 * 
 * @param type '/checkout' 
 * @param type function( 
 * @return type
 */
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

	if (!isset($_POST['desnumber']) || $_POST['desnumber'] === '')
	{
		Cart::setMsgError("Informe o número.");

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

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		"idcart"=>$cart->getidcart(),
		"idaddress"=>$address->getidaddress(),
		"iduser"=>$user->getiduser(),
		"idstatus"=>OrderStatus::OPENED,
		"vltotal"=>$cart->getvltotal()
	]);

	$order->save();

	switch ((int)$_POST['payment-method']) {
		case 1:
			header("Location: /order/" . $order->getidorder() . "/pagseguro");
			break;
		case 2:
			header("Location: /order/" . $order->getidorder() . "/paypal");
			break;
		
		default:
			break;
	}

	exit;

});

/**
 * 
 * @param type '/order/:idorder/paypal' 
 * @param type function($idorder 
 * @return type
 */
$app->get('/order/:idorder/paypal', function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new Page([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("payment-paypal", [
		"order"=>$order->getValues(),
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts()
	]);

});

 ?>