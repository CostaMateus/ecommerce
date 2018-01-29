<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;


/**
 * Rota da página que lista as categorias 
 * @param type '/admin/categories' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/categories', function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
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

 ?>