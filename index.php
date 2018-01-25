<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

// carrega pagina do admin logado
$app->get('/admin', function() {
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

// carrega pagina de login
$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});

// valida dados de login
$app->post('/admin/login', function() {
    
    User::login($_POST["deslogin"], $_POST["despassword"]);

    header("Location: /admin");
    exit; 

});

$app->run();

 ?>