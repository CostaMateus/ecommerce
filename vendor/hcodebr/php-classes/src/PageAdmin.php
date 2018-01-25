<?php 

namespace Hcode;

class PageAdmin extends Page 
{
	/**
	 * Construtor da PageAdmin, que invoca o da Page
	 * @param type|array $opts 
	 * @param type|string $tpl_dir 
	 * @return type
	 */
	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{
		parent::__construct($opts, $tpl_dir);
	}

}
 ?>