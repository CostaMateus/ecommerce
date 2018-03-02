<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;


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

	$page->setTpl("cart");

});



?>