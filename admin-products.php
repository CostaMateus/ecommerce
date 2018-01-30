<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;


/**
 * Rota da página que lista os produtos 
 * @param type '/admin/categories' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/products', function(){

	User::verifyLogin();

	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		'products'=>$products
	]);
});

/**
 * Rota da página que adiciona novo produto
 * @param type '/admin/products/create' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/products/create', function(){

	User::verifyLogin();

	$page = new PageAdmin(); 

	$page->setTpl("products-create");;

});

/**
 * Rota _POST da página que adiciona novo produto, insere no banco
 * @param type '/admin/products/create' 
 * @param type function( 
 * @return type
 */
$app->post('/admin/products/create', function(){

	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	(($_FILES['desimage'] == null) ? $product->setImage($_FILES['desimage']) :  $product->setImage());
	
	$product->save();

	header("Location: /admin/products");
	exit;

});

/**
 * Rota DELETE da pagina que exibe os dados de um produto, apaga do banco
 * @param type '/admin/products/:idproduct/delete' 
 * @param type function($idproduct 
 * @return type
 */
$app->get('/admin/products/:idproduct/delete', function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /admin/products");
	exit;

});

/**
 * Rota da página que exibe os dados de um produto
 * @param type '/admin/products/:idproduct' 
 * @param type function($idproduct 
 * @return type
 */
$app->get('/admin/products/:idproduct', function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", [
		'product'=>$product->getValues()
	]);

});

/**
 * Rota _POST da página que exibe os dados de um produto, atualiza no banco
 * @param type '/admin/products/:idproduct' 
 * @param type function($idproduct 
 * @return type
 */
$app->post('/admin/products/:idproduct', function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	((!$_FILES['desimage'] == null) ? $product->setImage($_FILES['desimage']) :  $product->setImage());

	$product->update();

	header("Location: /admin/products");
	exit;

});

 ?>