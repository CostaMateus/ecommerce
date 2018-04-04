<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;


/**
 * 
 * @param type $date 
 * @return type
 */
function formatDate($date)
{
	return date('d/m/Y', strtotime($date));
}

/**
 * Formata o valor do dinheiro para reais (R$) 
 * @param float $vlprice 
 * @return type
 */
function formatPrice($vlprice)
{
	if (!$vlprice > 0) $vlprice = 0;

	return number_format($vlprice, 2, ",", ".");
}

/**
 * 
 * @param type|bool $inadmin 
 * @return type
 */
function checklogin($inadmin = true)
{
	return User::checklogin($inadmin);
}

/**
 * 
 * @return type
 */
function getUserName()
{
	$user = User::getFromSession();

	return $user->getdesperson();
}

/**
 * 
 * @return type
 */
function getCartNrQtd()
{
	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];
}

/**
 * 
 * @return type
 */
function getCartVlSubTotal()
{
	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();
	
	return formatPrice($totals['vlprice']);
}

 ?>