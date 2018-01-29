<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;


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

 ?>