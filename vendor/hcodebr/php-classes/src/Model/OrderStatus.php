<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class OrderStatus extends Model 
{

	const OPENED 			= 1;
	const AWAITING_PAYMENT 	= 2;
	const PAID 				= 3;
	const DELIVERED 		= 4;
	const CANCELED 			= 5;

	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_ordersstatus ORDER BY desstatus");
	}

}

 ?>