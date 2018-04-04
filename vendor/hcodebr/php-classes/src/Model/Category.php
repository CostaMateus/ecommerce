<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model 
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
			FROM tb_categories 
			ORDER BY descategory;");
	}

	/**
	 * 
	 * @return type
	 */
	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_categories_save(:IDCATEGORY, :DESCATEGORY)", [
			":IDCATEGORY"=>$this->getidcategory(),
			":DESCATEGORY"=>$this->getdescategory()
		]);

		$this->setData($r[0]);

		Category::updateFile();
	}

	/**
	 * 
	 * @param type $idcategory 
	 * @return type
	 */
	public function get($idcategory)
	{
		$sql = new Sql();

		$r = $sql->select("
			SELECT * 
			FROM tb_categories 
			WHERE idcategory = :IDCATEGORY;", [
			":IDCATEGORY"=>$idcategory
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

		$sql->query("
			DELETE FROM tb_categories 
			WHERE idcategory = :IDCATEGORY;", [
			":IDCATEGORY"=>$this->getidcategory()
		]);

		Category::updateFile();
	}

	/**
	 * 
	 * @return type
	 */
	public static function updateFile()
	{
		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
	}

	/**
	 * 
	 * @param type|bool $related 
	 * @return type
	 */
	public function getProducts($related = true)
	{
		$sql = new Sql();

		if ($related === true) 
		{
			return $sql->select("
				SELECT * 
				FROM tb_products 
				WHERE idproduct IN (
					SELECT a.idproduct 
					FROM tb_products a 
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
					WHERE b.idcategory = :IDCATEGORY
				);", [
				":IDCATEGORY"=>$this->getidcategory()
			]);
		}
		else 
		{
			return $sql->select("
				SELECT * 
				FROM tb_products 
				WHERE idproduct NOT IN (
					SELECT a.idproduct 
					FROM tb_products a 
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
					WHERE b.idcategory =:IDCATEGORY
				);", [
				":IDCATEGORY"=>$this->getidcategory()
			]);
		}
	}

	/**
	 * 
	 * @param type $page 
	 * @param type|int $itemsPerPage 
	 * @return type
	 */
	public function getProductsPage($page = 1, $itemsPerPage = 12)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$r = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_products a 
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory 
			WHERE c.idcategory = :IDCATEGORY 
			LIMIT $start, $itemsPerPage;", [
				":IDCATEGORY"=>$this->getidcategory()
			]);

		$rtotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			"data"=>$r,
			"total"=>(int)$rtotal[0]['nrtotal'],
			"pages"=>ceil($rtotal[0]['nrtotal'] / $itemsPerPage)
		];

	}

	/**
	 * 
	 * @param Product $product 
	 * @return type
	 */
	public function addProduct(Product $product) 
	{
		$sql = new Sql();

		$sql->query("
			INSERT INTO tb_productscategories (idcategory, idproduct) 
			VALUES (:IDCATEGORY, :IDPRODUCT)", [
			":IDCATEGORY"=>$this->getidcategory(),
			":IDPRODUCT"=>$product->getidproduct()
		]);
	}

	/**
	 * 
	 * @param Product $product 
	 * @return type
	 */
	public function removeProduct(Product $product) 
	{
		$sql = new Sql();

		$sql->query("
			DELETE FROM tb_productscategories 
			WHERE idcategory = :IDCATEGORY AND idproduct = :IDPRODUCT", [
			":IDCATEGORY"=>$this->getidcategory(),
			":IDPRODUCT"=>$product->getidproduct()
		]);
	}
	
}