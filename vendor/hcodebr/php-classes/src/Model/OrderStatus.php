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

}

 ?>