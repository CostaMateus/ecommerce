<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model 
{	
	const SESSION = "Cart";

	/**
	 * 
	 * @return type
	 */
	public static function getFromSession()
	{
		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0)
		{
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
		}
		else 
		{
			$cart->getFrromSessionID();

			if (!(int)$cart->getidcart() > 0)
			{
				$data = [
					"dessessionid"=>session_id()
				];

				if (User::checkLogin(false))
				{
					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();
				}
				
				$cart->setData($data);

				$cart->save();

				$cart->setToSession();
			}
		}

		return $cart;
	}

	/**
	 * 
	 * @return type
	 */
	public function setToSession() 
	{
		$_SESSION[Cart::SESSION] = $this->getValues();
	}

	/**
	 * 
	 * @return type
	 */
	public function getFromSessionID()
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			":dessessionid"=>session_id()
		]);

		if (count($r) > 0) 
		{
			$this->setData($r[0]);
		}
	}

	/**
	 * 
	 * @param int $idcart 
	 * @return type
	 */
	public function get(int $idcart)
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			":idcart"=>$idcart
		]);

		if (count($r) > 0) 
		{
			$this->setData($r[0]);
		}
	}

	/**
	 * Salva carrinho no banco
	 * @return type
	 */
	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			":idcart"=>$this->getidcart(),
			":dessessionid"=>$this->getdessessionid(),
			":iduser"=>$this->getiduser(),
			":deszipcode"=>$this->getdeszipcode(),
			":vlfreight"=>$this->getvlfreight(),
			":nrdays"=>$this->getnrdays(),
		]);

		$this->setData($r[0]);
	}

	/**
	 * Adiciona um produto ao carrinho
	 * @param Product $product 
	 * @return type
	 */
	public function addProduct(Product $product)
	{
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		]);
	}

	/**
	 * 
	 * @param Product $product 
	 * @param type|bool $all 
	 * @return type
	 */
	public function removeProduct(Product $product, $all = false)
	{
		$sql = new Sql();

		if ($all) 
		{
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);
		}
		else
		{
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);
		}
	}

	/**
	 * 
	 * @return type
	 */
	public function getProducts()
	{
		$sql = new Sql();

		$rs = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, b.desimage, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, b.desimage 
			ORDER BY b.desproduct", [
				":idcart"=>$this->getidcart()
			]);

		return $rs;
	}
}

 ?>