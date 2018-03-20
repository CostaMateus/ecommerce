<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Address;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


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
 * Rota da página de login de usuário no site
 * @param type '/login' 
 * @param type function( 
 * @return type
 */
$app->get('/login', function(){

	$page = new Page();

	$page->setTpl("login", [
		"error"=>User::getMsgError(),
		"errorRegister"=>User::getErrorRegister(),
		"registerValues"=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ["name"=>"", "email"=>"", "phone"=>""]
	]);

});

/**
 * Rota _POST da página de login de usuário no site
 * @param type '/login' 
 * @param type function( 
 * @return type
 */
$app->post('/login', function(){

	try {
	
		User::login($_POST['login'], $_POST['password']);
	
	} catch (Exception $e) {

		User::setMsgError($e->getMessage());

	}

	header("Location: /");
	exit;

});

/**
 * Rota que efetua o logout de um usuário
 * @param type '/logout' 
 * @param type function( 
 * @return type
 */
$app->get('/logout', function(){
	
	User::logout();

	header("Location: /login");
	exit;

});

/**
 * 
 * @param type '/register' 
 * @param type function( 
 * @return type
 */
$app->post('/register', function(){

	$_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['name']) || $_POST['name'] == '')
	{
		User::setErrorRegister("Insira o seu nome.");

		header("Location: /login");
		exit;
	}

	if (!isset($_POST['email']) || $_POST['email'] == '')
	{
		User::setErrorRegister("Insira um e-mail válido.");

		header("Location: /login");
		exit;
	}

	if (!isset($_POST['password']) || $_POST['password'] == '')
	{
		User::setErrorRegister("Insira uma senha.");

		header("Location: /login");
		exit;
	}

	if (User::checkLoginExist($_POST['email']) === true)
	{
		User::setErrorRegister("Este e-mail já está em uso!");

		header("Location: /login");
		exit;
	}
	
	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'], 
		'desperson'=>$_POST['name'], 
		'desemail'=>$_POST['email'], 
		'despassword'=>$_POST['password'], 
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header("Location: /checkout");
	exit;

});

/**
 * Rota do perfil do usuário do site
 * @param type '/profile' 
 * @param type function( 
 * @return type
 */
$app->get('/profile', function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getMsgSuccess(),
		'profileError'=>User::getMsgError()
	]);

});

/**
 * Rota _POST do perfil do usuário, alteração de dados cadastrais
 * @param type '/profile' 
 * @param type function( 
 * @return type
 */
$app->post('/profile', function(){

	User::verifyLogin();

	if (!isset($_POST['despersi]on']) || $_POST['desperson'] === '')
	{
		User::setMsgError("Preencha o seu nome.");
		header("Location: /profile");
		exit;
	}
	if (!isset($_POST['desemail']) || $_POST['desemail'] === '')
	{
		User::setMsgError("Preencha o seu e-mail.");
		header("Location: /profile");
		exit;
	}

	$user = User::getFromSession();

	if ($_POST['desemail'] !== $user->getdesemail())
	{
		if (User::checkLoginExist($_POST['desemail']) === true)
		{
			User::setMsgError("Este endereço de e-mail já está cadastrado.");
			header("Location: /profile");
			exit;
		} 
	}


	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->save();

	User::setMsgSuccess("Dados alterados com sucesso!");

	header("Location: /profile");
	exit;

});

$app->get('/order/:idorder', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		"order"=>$order->getValues()
	]);
});


$app->get('/boleto/:idorder', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(".", "",$valor_cobrado);
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
	$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " " . $order->getdescountry() . " " . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 5 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@costamateus.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - ecommerce.com";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1035"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "03948";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "4"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");

});


 ?>