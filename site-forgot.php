<?php

use \Hcode\Page;
use \Hcode\Model\User;


/**
 * Rota da página de 'esqueci a senha'
 * @param type '/forgot' 
 * @param type function( 
 * @return type
 */
$app->get('/forgot' , function() {

	$page = new Page();

	$page->setTpl("forgot");

});

/**
 * Rota _POST da pagina de 'esqueci a senha', envia o email
 * @param type '/forgot' 
 * @param type function( 
 * @return type
 */
$app->post('/forgot' , function() {

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

/**
 * Rota da página que confirma o envio do email de 'esqueci a senha'
 * @param type '/forgot/sent' 
 * @param type function( 
 * @return type
 */
$app->get('/forgot/sent' , function() {

	$page = new Page();

	$page->setTpl("forgot-sent");

});

/**
 * Rota da página que reseta a senha do usuário
 * @param type '/forgot/reset' 
 * @param type function( 
 * @return type
 */
$app->get('/forgot/reset' , function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

/**
 * Rota _POST da página que reseta a senha do usuário, confirma o reset
 * @param type '/forgot/reset' 
 * @param type function( 
 * @return type
 */
$app->post('/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST['password']);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

 ?>