<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

/**
 * Rota padrão da página inicial do site
 * @param type '/' 
 * @param type function( 
 * @return type
 */
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

/**
 * Rota da página inicial do usuário admin
 * @param type '/admin' 
 * @param type function( 
 * @return type
 */
$app->get('/admin', function() {
    
    User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

/**
 * Rota da página de login do usuário admin
 * @param type '/admin/login' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});

/**
 * Rota _POST da página de login do usuário admin, valida o login 
 * @param type '/admin/login' 
 * @param type function( 
 * @return type
 */
$app->post('/admin/login', function() {
    
    User::login($_POST["deslogin"], $_POST["despassword"]);

    header("Location: /admin");
    exit; 

});

/**
 * Rota que efetua o logout do usuário admin
 * @param type '/admin/logout' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/logout', function() {
	
	User::logout();

	header("Location: /admin/login");
	exit;
});

/**
 * Rota da página que lista usuários
 * @param type '/admin/users' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/users', function() {

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));
	
});

/**
 * Rota da página que adicina novo usuário
 * @param type '/admin/users/create' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/users/create', function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
	
});

/**
 * Rota _POST da página que adiciona novo usuário, insere no banco
 * @param type '/admin/users/create' 
 * @param type function( 
 * @return type
 */
$app->post('/admin/users/create', function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$ops = [
		"cost"=>10
	];
	
	$_POST["despassword"] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, $ops);

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});

/**
 * Rota DELETE da página que exibe dados de um usuário, apaga do banco
 * @param type '/admin/users/:iduser' 
 * @param type function($iduser 
 * @return type
 */
$app->get('/admin/users/:iduser/delete', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

/**
 * Rota da página que exibi dados de um usuário
 * @param type '/admin/users/:iduser' 
 * @param type function($iduser 
 * @return type
 */
$app->get('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();
	
	$user = new User();
	
	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

/**
 * Rota _POST da página que exibi dados de um usuário, atualiza no banco
 * @param type '/admin/users/:iduser' 
 * @param type function($iduser 
 * @return type
 */
$app->post('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();

	header("Location: /admin/users");
	exit;

});

/**
 * Rota da página de 'esqueci a senha'
 * @param type '/admin/forgot' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/forgot' , function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

/**
 * Rota _POST da pagina de 'esqueci a senha', envia o email
 * @param type '/admin/forgot' 
 * @param type function( 
 * @return type
 */
$app->post('/admin/forgot' , function() {

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});

/**
 * Rota da página que confirma o envio do email de 'esqueci a senha'
 * @param type '/admin/forgot/sent' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/forgot/sent' , function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

/**
 * Rota da página que reseta a senha do usuário
 * @param type '/admin/forgot/reset' 
 * @param type function( 
 * @return type
 */
$app->get('/admin/forgot/reset' , function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

/**
 * Rota _POST da página que reseta a senha do usuário, confirma o reset
 * @param type '/admin/forgot/reset' 
 * @param type function( 
 * @return type
 */
$app->post('/admin/forgot/reset' , function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$ops = [
		"cost"=>10
	];

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, $ops);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});

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

$app->run();

 ?>