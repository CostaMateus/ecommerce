<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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


$app->run();

 ?>