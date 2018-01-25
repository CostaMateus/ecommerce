<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{
	
	public static function login($login, $password)
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			"LOGIN"=>$login
		));

		if (count($r) === 0) 
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $r[0];

		if (password_verify($password, $data["despassword"]) === true) 
		{
			$user = new User();

			$user->setIdUser($data["iduser"]);

		} else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}
}

?>