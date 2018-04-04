<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use Upload\Upload;

class Product extends Model 
{

	/**
	 * Busca no banco todos os registros de categorias
	 * @return type
	 */
	public static function listAll() 
	{
		$sql = new Sql();

		return $sql->select("
			SELECT * 
			FROM tb_products 
			ORDER BY idproduct;");
	}

	/**
	 * 
	 * @return type
	 */
	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_products_save(:DESPRODUCT, :VLPRICE, :VLWIDTH, :VLHEIGHT, :VLLENGTH, :VLWEIGHT, :DESURL, :DESIMAGE)", [
			":DESPRODUCT"=>$this->getdesproduct(),
			":VLPRICE"=>$this->getvlprice(),
			":VLWIDTH"=>$this->getvlwidth(),
			":VLHEIGHT"=>$this->getvlheight(),
			":VLLENGTH"=>$this->getvllength(),
			":VLWEIGHT"=>$this->getvlweight(),
			":DESURL"=>$this->getdesurl(),
			":DESIMAGE"=>$this->getdesimage()
		]);

		$this->setData($r[0]);
	}

	/**
	 * 
	 * @param type $idproduct 
	 * @return type
	 */
	public function get($idproduct)
	{
		$sql = new Sql();

		$r = $sql->select("
			SELECT * 
			FROM tb_products 
			WHERE idproduct = :IDPRODUCT;", [
			":IDPRODUCT"=>$idproduct
		]);

		$this->setData($r[0]);
	}

	/**
	 * 
	 * @return type
	 */
	public function update()
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_productsupdate_save(:IDPRODUCT, :DESPRODUCT, :VLPRICE, :VLWIDTH, :VLHEIGHT, :VLLENGTH, :VLWEIGHT, :DESURL, :DESIMAGE)", [
			":IDPRODUCT"=>$this->getidproduct(),
			":DESPRODUCT"=>$this->getdesproduct(),
			":VLPRICE"=>$this->getvlprice(),
			":VLWIDTH"=>$this->getvlwidth(),
			":VLHEIGHT"=>$this->getvlheight(),
			":VLLENGTH"=>$this->getvllength(),
			":VLWEIGHT"=>$this->getvlweight(),
			":DESURL"=>$this->getdesurl(),
			":DESIMAGE"=>$this->getdesimage()
		]);

		$this->setData($r[0]);
	}

	/**
	 * 
	 * @return type
	 */
	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_products_delete(:IDPRODUCT)", [
			":IDPRODUCT"=>$this->getidproduct()
		]);
	}

	/**
	 * 
	 * @param type|null $file 
	 * @return type
	 */
	public function setImage($file = null)
	{
		if ($file === null) 
		{
			$this->setdesimage("default.jpg");
		} 
		else 
		{
			$handle = new Upload($file);

			$handle->file_safe_name = true;

			$dir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
				"res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . 
				"img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR;

			$handle->Process($dir);

			$this->setdesimage($handle->file_dst_name);

			$handle->Clean();
		}
	}

	/**
	 * 
	 * @param type $desurl 
	 * @return type
	 */
	public function getFromURL($desurl)
	{
		$sql = new Sql();

		$r = $sql->select("
			SELECT * 
			FROM tb_products 
			WHERE desurl = :DESURL 
			LIMIT 1", [
			":DESURL"=>$desurl
		]);

		$this->setData($r[0]);
	}

	/**
	 * 
	 * @return type
	 */
	public function getCategories()
	{
		$sql = new Sql();

		return $sql->select("
			SELECT * 
			FROM tb_categories a 
			INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory 
			WHERE b.idproduct = :IDPRODUCT", [
				":IDPRODUCT"=>$this->getidproduct()
			]);
	}
}