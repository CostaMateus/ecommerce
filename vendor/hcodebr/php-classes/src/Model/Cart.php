<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model 
{	
	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

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
	 * Retorna o carrinho
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
	 * Adiciona produto ao carrinho
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

		$this->getCalculateTotal();
	}

	/**
	 * Remove produto do carrinho
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

		$this->getCalculateTotal();
	}

	/**
	 * Retorna os produtos do carrinho
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

	/**
	 * Calcula o valor total do carrinho
	 * @return type
	 */
	public function getProductsTotals() 
	{
		$sql = new Sql();

		$r = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd 
			FROM tb_products a 
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct 
			WHERE b.idcart = :idcart AND dtremoved IS NULL;", [
				":idcart"=>$this->getidcart()
			]);

		if (count($r) > 0) 
		{
			return $r[0];
		}
		else 
		{
			return [];
		}
	}

	/**
	 * Calcula valor do frete
	 * @param type $nrzipcode 
	 * @return type
	 */
	public function setFreight($nrzipcode)
	{
		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if ($totals['nrqtd'] > 0)
		{
			if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;

			if ($totals['vllength'] < 16) $totals['vllength'] = 16;
			
			$qs = http_build_query([
				"nCdEmpresa"=>"",
				"sDsSenha"=>"",
				"nCdServico"=>"40010",
				"sCepOrigem"=>"01224001",
				"sCepDestino"=>$nrzipcode,
				"nVlPeso"=>$totals['vlweight'],
				"nCdFormato"=>"1",
				"nVlComprimento"=>$totals['vllength'],
				"nVlAltura"=>$totals['vlheight'],
				"nVlLargura"=>$totals['vlwidth'],
				"nVlDiametro"=>"0",
				"sCdMaoPropria"=>"S",
				"nVlValorDeclarado"=>$totals['vlprice'],
				"sCdAvisoRecebimento"=>"S"
			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			$r = $xml->Servicos->cServico;
			
			if ($r->MsgErro != "")
			{
				Cart::setMsgError($r->MsgErro);
			}
			else
			{
				Cart::clearMsgError();
			}

			$this->setnrdays($r->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($r->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $r;
		}
		else 
		{

		}
	}

	/**
	 * 
	 * @return type
	 */
	public function updateFreight()
	{
		if ($this->getdeszipcode() != "")
		{
			$this->setFreight($this->getdeszipcode());
		}
	}


	public function getValues()
	{
		$this->getCalculateTotal();

		return parent::getValues();
	}

	public function getCalculateTotal()
	{

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());

	}



	/**
	 * Formata valor do frete retornado pelo webservice dos Correios
	 * @param type $value 
	 * @return type
	 */
	public static function formatValueToDecimal($value):float
	{
		$value = str_replace('.', '', $value);

		return str_replace(',', '.', $value);
	}

	/**
	 * Altera mensagem de erro da constante
	 * @param type $msg 
	 * @return type
	 */
	public static function setMsgError($msg)
	{
		$_SESSION[Cart::SESSION_ERROR] = $msg;
	}

	/**
	 * Retorna mensagem de erro que está na constante
	 * @return type
	 */
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;
	}

	/**
	 * Apaga mensagem de erro da constante
	 * @return type
	 */
	public static function clearMsgError()
	{
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}
}

 ?>