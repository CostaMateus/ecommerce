<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model 
{

	/**
	 * Busca no banco todos os registros de categorias
	 * @return type
	 */
	public static function listAll() 
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct;");
	}

	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlheight, :vllength, :vlweight, :desurl;", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		$this->setData($r[0]);
	}

	public function get($idproduct)
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct;", [
			":idproduct"=>$idproduct
		]);

		$this->setData($r[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idproduct = :idproduct;", [
			":idproduct"=>$this->getidproduct()
		]);

	}
}