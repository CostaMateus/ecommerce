<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;


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


 ?>