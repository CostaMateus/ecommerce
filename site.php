<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;


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

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);
});


?>