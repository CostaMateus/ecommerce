<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


/**
 * 
 * @param type '/admin/orders/:idorder/delete' 
 * @param type function($idorder 
 * @return type
 */
$app->get('/admin/orders/:idorder/delete', function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /admin/orders");
	exit;
	
});

/**
 * 
 * @param type '/admin/orders/:idorder/status' 
 * @param type function($idorder 
 * @return type
 */
$app->get('/admin/orders/:idorder/status', function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl("order-status", [
		"order"=>$order->getValues(),
		"status"=>OrderStatus::listAll(),
		"msgError"=>Order::getMsgError(),
		"msgSuccess"=>Order::getMsgSuccess()
	]);

});

/**
 * 
 * @param type '/admin/orders/:idorder/status' 
 * @param type function($idorder 
 * @return type
 */
$app->post('/admin/orders/:idorder/status', function($idorder){

	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0)
	{
		Order::setMsgError("Informe o status atual.");
		header("Location: /admin/orders/" . $idorder . "/status");
		exit;
	}

	$order = new Order();

	$order->get((int)$idorder);

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setMsgSuccess("Status atualizado.");

	header("Location: /admin/orders/" . $idorder . "/status");
	exit;

});

/**
 * 
 * @param type '/admin/orders/:idorder' 
 * @param type function($idorder 
 * @return type
 */
$app->get('/admin/orders/:idorder', function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order", [
		"order"=>$order->getValues(),
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts()
	]);

});

/**
 * 
 * @param type '/admin/orders' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/orders', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("orders", [
		"orders"=>Order::listAll()
	]);

});

 ?>