<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model 
{

	/**
	 * Busca no banco todos os registros de categorias
	 * @return type
	 */
	public static function listAll() 
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory;");
	}

	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setData($r[0]);
	}

	public function get($idcategory)
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory;", [
			":idcategory"=>$idcategory
		]);

		$this->setData($r[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory;", [
			":idcategory"=>$this->getidcategory()
		]);
	}
}