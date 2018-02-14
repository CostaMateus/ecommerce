<?php

/**
 * Função que formata do dinheiro para reais (R$) 
 * @param float $vlprice 
 * @return type
 */
function formatPrice(float $vlprice)
{
	return number_format($vlprice, 2, ",", ".");
}

 ?>