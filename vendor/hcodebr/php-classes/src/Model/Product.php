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

		return $sql->select("SELECT * FROM tb_products ORDER BY idproduct;");
	}

	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_products_save(:desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl, :desimage)", [
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl(),
			":desimage"=>$this->getdesimage()
		]);

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

	public function update()
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_productsupdate_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl, :desimage)", [
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl(),
			":desimage"=>$this->getdesimage()
		]);

		$this->setData($r[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_products_delete(:idproduct)", [
			":idproduct"=>$this->getidproduct()
		]);

	}

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

	public function getFromURL($desurl)
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
			":desurl"=>$desurl
		]);

		$this->setData($r[0]);
	}

	public function getCategories()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories a 
			INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory 
			WHERE b.idproduct = :idproduct", [
				":idproduct"=>$this->getidproduct()
			]);
	}
}