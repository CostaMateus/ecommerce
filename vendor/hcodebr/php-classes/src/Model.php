<?php

namespace Hcode;

class Model 
{
	private $values = [];

	/**
	 * Metodo magico, substitui todos os gets e sets
	 * @param type $name 
	 * @param type $args 
	 * @return type
	 */
	public function __call($name, $args) 
	{
		$method = substr($name, 0, 3);

		$fieldName = substr($name, 3, strlen($name));

		switch ($method) {
			case 'get':
				return $this->values[$fieldName];
				break;
			case 'set':
				$this->values[$fieldName] = $args[0];
				break;
		}
	}

	/**
	 * Metodo magico, seta todos os campos oriundos do banco
	 * @param type|array $data 
	 * @return type
	 */
	public function setData($data = array()) 
	{
		foreach ($data as $key => $value) 
		{
			$this->{"set".$key}($value);
		}
	}

	/**
	 * Retorno os atributos dentro do array
	 * @return type
	 */
	public function getValues()
	{
		return $this->values;
	}

}
 ?>