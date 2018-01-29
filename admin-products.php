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


 ?>