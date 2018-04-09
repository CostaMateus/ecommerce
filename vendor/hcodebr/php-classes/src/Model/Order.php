<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\Cart;

class Order extends Model 
{
	const SUCCESS = "Order-success";
	const ERROR = "Order-error";


	/**
	 * Salva pedido no banco
	 * @return type
	 */
	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_orders_save(:IDORDER, :IDCART, :IDUSER, :IDSTATUS, :IDADDRESS, :VLTOTAL);", [
			":IDORDER"=>$this->getidorder(),
			":IDCART"=>$this->getidcart(),
			":IDUSER"=>$this->getiduser(),
			":IDSTATUS"=>$this->getidstatus(),
			":IDADDRESS"=>$this->getidaddress(),
			":VLTOTAL"=>$this->getvltotal()
		]);

		if (count($r) > 0) 
		{
			$this->setData($r[0]);
		}
	}

	/**
	 * Exclui pedido no banco
	 * @return type
	 */
	public function delete()
	{
		$sql = new SQL();

		$sql->query("DELETE FROM tb_orders WHERE idorder = :IDORDER", [
			":IDORDER"=>$this->getidorder()
		]);
	}

	/**
	 * Retorna um pedido, dado o id
	 * @param type $idorder 
	 * @return type
	 */
	public function get($idorder)
	{
		$sql = new Sql();

		$r = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :IDORDER", [
				":IDORDER"=>$idorder
			]);

		if (count($r) > 0)
		{
			$this->setData($r[0]);
		}
	}

	/**
	 * Retorna todos os pedidos
	 * @return type
	 */
	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			ORDER BY a.dtregister DESC
			");
	}

	/**
	 * 
	 * @return type
	 */
	public function getCart()
	{
		$cart = new Cart();

		$cart->get((int)$this->getidcart());

		return $cart;
	}


	/**
	 * Altera mensagem de sucesso da constante
	 * @param type $msg 
	 * @return type
	 */
	public static function setMsgSuccess($msg)
	{

		$_SESSION[Order::SUCCESS] = $msg;

	}

	/**
	 * Retorna mensagem de sucesso que está na constante
	 * @return type
	 */
	public static function getMsgSuccess()
	{
		$msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : "";

		Order::clearMsgSuccess();

		return $msg;
	}

	/**
	 * Apaga mensagem de sucesso da constante
	 * @return type
	 */
	public static function clearMsgSuccess()
	{
		
		$_SESSION[Order::SUCCESS] = NULL;
	
	}

	/**
	 * Altera mensagem de erro da constante
	 * @param type $msg 
	 * @return type
	 */
	public static function setMsgError($msg)
	{

		$_SESSION[Order::ERROR] = $msg;

	}

	/**
	 * Retorna mensagem de erro que está na constante
	 * @return type
	 */
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : "";

		Order::clearMsgError();

		return $msg;
	}

	/**
	 * Apaga mensagem de erro da constante
	 * @return type
	 */
	public static function clearMsgError()
	{
		
		$_SESSION[Order::ERROR] = NULL;
	
	}

	/**
	 * 
	 * @param type $page 
	 * @param type|int $itemsPerPage 
	 * @return type
	 */
	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$r = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			ORDER BY a.dtregister DESC 
			LIMIT $start, $itemsPerPage;");

		$rtotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			"data"=>$r,
			"total"=>(int)$rtotal[0]['nrtotal'],
			"pages"=>ceil($rtotal[0]['nrtotal'] / $itemsPerPage)
		];
	}

	/**
	 * 
	 * @param type $page 
	 * @param type|int $itemsPerPage 
	 * @return type
	 */
	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$r = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :ID 
			OR f.desperson LIKE :SEARCH 
			ORDER BY a.dtregister DESC 
			LIMIT $start, $itemsPerPage;", [
				":SEARCH"=>"%" . $search . "%",
				":ID"=>$search
			]);

		$rtotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			"data"=>$r,
			"total"=>(int)$rtotal[0]['nrtotal'],
			"pages"=>ceil($rtotal[0]['nrtotal'] / $itemsPerPage)
		];
	}
}

 ?>