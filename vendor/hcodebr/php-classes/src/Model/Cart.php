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

		$r = $sql->select("
			SELECT * 
			FROM tb_carts 
			WHERE dessessionid = :DESSESSIONID", [
			":DESSESSIONID"=>session_id()
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

		$r = $sql->select("
			SELECT * 
			FROM tb_carts 
			WHERE idcart = :idcart", [
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

		$r = $sql->select("CALL sp_carts_save(:IDCART, :DESSESSIONID, :IDUSER, :DESZIPCODE, :VLFREIGHT, :NRDAYS)", [
			":IDCART"=>$this->getidcart(),
			":DESSESSIONID"=>$this->getdessessionid(),
			":IDUSER"=>$this->getiduser(),
			":DESZIPCODE"=>$this->getdeszipcode(),
			":VLFREIGHT"=>$this->getvlfreight(),
			":NRDAYS"=>$this->getnrdays(),
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

		$sql->query("
			INSERT INTO tb_cartsproducts (idcart, idproduct) 
			VALUES (:IDCART, :IDPRODUCT)", [
			":IDCART"=>$this->getidcart(),
			":IDPRODUCT"=>$product->getidproduct()
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
			$sql->query("
				UPDATE tb_cartsproducts 
				SET dtremoved = NOW() 
				WHERE idcart = :IDCART AND idproduct = :IDPRODUCT AND dtremoved IS NULL", [
				":IDCART"=>$this->getidcart(),
				":IDPRODUCT"=>$product->getidproduct()
			]);
		}
		else
		{
			$sql->query("
				UPDATE tb_cartsproducts 
				SET dtremoved = NOW() 
				WHERE idcart = :IDCART AND idproduct = :IDPRODUCT AND dtremoved IS NULL LIMIT 1", [
				":IDCART"=>$this->getidcart(),
				":IDPRODUCT"=>$product->getidproduct()
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

		$rs = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, b.desimage, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :IDCART AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, b.desimage 
			ORDER BY b.desproduct", [
				":IDCART"=>$this->getidcart()
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
			WHERE b.idcart = :IDCART AND dtremoved IS NULL;", [
				":IDCART"=>$this->getidcart()
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
			// INICIO CODIGO ORIGINAL AULA
			
			//Height (Altura)      máximo: 105cm
			//Width  (Largura)     máximo: 105cm
			//Length (Comprimento) máximo: 105cm
			//Weight (Peso)        máximo: 30kg
			//Somatório das dimensões (A + L + C) não deve superar: 200cm

			if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;

			if ($totals['vlwidth'] < 11) $totals['vlwidth'] = 11;

			if ($totals['vllength'] < 16) $totals['vllength'] = 16;
			
			$qs = http_build_query([
				"nCdEmpresa"=>"",
				"sDsSenha"=>"",
				//"nCdServico"=>"40010",
				"nCdServico"=>"40010,40045,40215,40290,41106",
				"sCepOrigem"=>"01224001",
				"sCepDestino"=>$nrzipcode,
				"nVlPeso"=>$totals['vlweight'],
				"nCdFormato"=>"1",
				"nVlComprimento"=>$totals['vllength'],
				"nVlAltura"=>$totals['vlheight'],
				"nVlLargura"=>$totals['vlwidth'],
				"nVlDiametro"=>"0",
				"sCdMaoPropria"=>"N",
				"nVlValorDeclarado"=>$totals['vlprice'],
				"sCdAvisoRecebimento"=>"N"
			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
			
			// FIM CODIGO ORIGINAL AULA


			// codigos de teste - multiplos serviços em uma request (linha nCdServico:)
			/*
			foreach($xml -> cServico as $row) {
				if($row -> Erro == 0) 
				{
					echo $row -> Codigo . '<br>';
					echo $row -> Valor . '<br>';
					echo $row -> PrazoEntrega . '<br>';
					echo $row -> ValorMaoPropria . '<br>';
					echo $row -> ValorAvisoRecebimento . '<br>';
					echo $row -> ValorValorDeclarado . '<br>';
					echo $row -> EntregaDomiciliar . '<br>';
					echo $row -> EntregaSabado;
				} 
				else 
				{
					echo $row -> MsgErro;
				}
				echo '<hr>';
			}
			exit;
			*/
			// fim cod teste

			// INICIO CODIGO ORIGONAL DA AULA 
			
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
			
			// FIM CODIGO ORIGINAL DA AULA

			// Valores de teste pra qnd o 'CWS is down'
			/*$this->setnrdays(5); //5 dias
			$this->setvlfreight(Cart::formatValueToDecimal(25)); // R$25
			$this->setdeszipcode($nrzipcode); 
			$this->save();*/
		}
		else 
		{
			$this->setnrdays(0);
			$this->setvlfreight(0);
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

	/**
	 * 
	 * @return type
	 */
	public function getValues()
	{
		$this->getCalculateTotal();

		return parent::getValues();
	}

	/**
	 * 
	 * @return type
	 */
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