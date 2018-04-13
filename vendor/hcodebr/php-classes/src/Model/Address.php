<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model 
{	
	const SESSION_ERROR = "AddressError";

	/**
	 * 
	 * @param type $nrcep 
	 * @return type
	 */
	public static function getCEP($nrcep) 
	{
		$nrcep = str_replace("-", "", $nrcep);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = json_decode(curl_exec($ch), true);

		curl_close($ch);

		return $data;

	}

	/**
	 * 
	 * @param type $nrcep 
	 * @return type
	 */
	public function loadFromCEP($nrcep)
	{
		$data = Address::getCEP($nrcep);

		if (isset($data['logradouro']) && $data['logradouro']) 
		{
			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement('');
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($nrcep);
		}

	}

	/**
	 * 
	 * @return type
	 */
	public function save()
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_addresses_save(:IDADDRESS, :IDPERSON, :DESADDRESS, :DESNUMBER, :DESCOMPLEMENT, :DESCITY, :DESSTATE, :DESCOUNTRY, :DESZIPCODE, :DESDISTRICT)", [
			":IDADDRESS"=>$this->getidaddress(),
			":IDPERSON"=>$this->getidperson(),
			":DESADDRESS"=>utf8_decode($this->getdesaddress()),
			":DESNUMBER"=>$this->getdesnumber(),
			":DESCOMPLEMENT"=>utf8_decode($this->getdescomplement()),
			":DESCITY"=>utf8_decode($this->getdescity()),
			":DESSTATE"=>utf8_decode($this->getdesstate()),
			":DESCOUNTRY"=>utf8_decode($this->getdescountry()),
			":DESZIPCODE"=>$this->getdeszipcode(),
			":DESDISTRICT"=>utf8_decode($this->getdesdistrict())
		]);

		if (count($r) > 0) 
		{
			$this->setData($r[0]);
		}
	}


	/**
	 * Altera mensagem de erro da constante
	 * @param type $msg 
	 * @return type
	 */
	public static function setMsgError($msg)
	{
		$_SESSION[Address::SESSION_ERROR] = $msg;
	}

	/**
	 * Retorna mensagem de erro que está na constante
	 * @return type
	 */
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::clearMsgError();

		return $msg;
	}

	/**
	 * Apaga mensagem de erro da constante
	 * @return type
	 */
	public static function clearMsgError()
	{
		$_SESSION[Address::SESSION_ERROR] = NULL;
	}
}

 ?>