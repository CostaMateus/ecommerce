<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


/**
 * Rota da página que lista as categorias 
 * @param type '/admin/categories' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/categories', function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') 
	{
		$pagination = Category::getPageSearch($search, $page);
	} 
	else
	{
		$pagination = Category::getPage($page);
	}

	$pages = [];

	for($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			"href"=>"/admin/categories?".http_build_query([
				"page"=>$x+1,
				"search"=>$search
			]),
			"text"=>$x+1
		]);
	}

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);
});

/**
 * Rota da página que adiciona nova categoria
 * @param type '/admin/categories/create' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/categories/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

/**
 * Rota _POST da página que adiciona nova categoria, insere no banco
 * @param type '/admin/categories/create' 
 * @param type function( 
 * @return type
 */
$app->post('/admin/categories/create', function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});

/**
 * Rota _POST da página que apaga uma categoria, deleta do banco
 * @param type '/admin/categories/:idcategory/delete' 
 * @param type function($idcategory 
 * @return type
 */
$app->get('/admin/categories/:idcategory/delete', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;

});

/**
 * Rota da página que edita uma categoria
 * @param type '/admin/categories/:idcategory' 
 * @param type function($idcategory 
 * @return type
 */
$app->get('/admin/categories/:idcategory', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]);

});

/**
 * Rota _POST da página que edita uma categoria, atualiza no banco
 * @param type '/admin/categories/:idcategory' 
 * @param type function($idcategory 
 * @return type
 */
$app->post('/admin/categories/:idcategory', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});

/**
 * Rota da página que lista os produtos relacionados ou não com uma dada categoria
 * @param type '/admin/categories/:idcategory/products' 
 * @param type function($idcategory 
 * @return type
 */
$app->get('/admin/categories/:idcategory/products', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);

});

/**
 * Rota da página que adiciona um produto a uma deterninada categoria
 * @param type '/admin/categories/:idcategory/products' 
 * @param type function($idcategory 
 * @return type
 */
$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/" . $idcategory . "/products");
	exit; 

});

/**
 * Rota da página que remove um produto de uma determinada categoria
 * @param type '/admin/categories/:idcategory/products' 
 * @param type function($idcategory 
 * @return type
 */
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/" . $idcategory . "/products");
	exit; 

});

 ?>