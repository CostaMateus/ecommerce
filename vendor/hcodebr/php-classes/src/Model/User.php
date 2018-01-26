<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model 
{	
	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const CIFRA = "AES-256-CBC";
	
	/**
	 * Valida login
	 * @param type $login 
	 * @param type $password 
	 * @return type
	 */
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

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();
			
			return $user;

		} else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}

	/**
	 * Verifica login para acessar area administrativa
	 * @param type|bool $inadmin 
	 * @return type
	 */
	public static function verifyLogin($inadmin = true) 
	{
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		) {
			header("Location: /admin/login");
			exit;
		} 
	}

	/**
	 * Efetua o logout do usuário
	 * @return type
	 */
	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}

	/**
	 * Busca no banco todos os registros de usuários
	 * @return type
	 */
	public static function listAll() 
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson;");
	}

	/**
	 * Salva novo registro de usuario no banco
	 * @return type
	 */
	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($r[0]);
	}

	/**
	 * Busca um registro de usuário por id no banco
	 * @param type $iduser 
	 * @return type
	 */
	public function get($iduser)
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$this->setData($r[0]);
	}

	/**
	 * Salva alteração no registro de um usuário no banco
	 * @return type
	 */
	public function update() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($r[0]);
	}

	/**
	 * Apaga registro de um usuário no banco
	 * @return type
	 */
	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}

	/**
	 * Envia email com codigo de recuperação de senha
	 * @param type $email 
	 * @param type|bool $inadmin 
	 * @return type
	 */
	public static function getForgot($email, $inadmin = true)
	{
		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;", array(
			":email"=>$email
		));

		if (count($r) === 0) 
		{
			throw new \Exception("Não foi possível recuperar a senha.");

		} else {
			
			$data = $r[0];

			$r2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if (count($r2) === 0) 
			{
				throw new \Exception("Não foi possível recuperar a senha.");
			} else {
				
				$dataRecovery = $r2[0];

				$IV = random_bytes(openssl_cipher_iv_length(User::CIFRA));
				
				$cryp = openssl_encrypt($dataRecovery['idrecovery'], User::CIFRA, User::SECRET, OPENSSL_RAW_DATA, $IV);

				$code = base64_encode($IV.$cryp);

				if ($inadmin === true) 
				{
					$link = "http://ecommerce.com/admin/forgot/reset?code=$code";
				
				} else {
					$link = "http://ecommerce.com/forgot/reset?code=$code";
				} 

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
				)); 

				$mailer->send();
				
				return $link;
			}
		}
	}

	/**
	 * Valida codigo de recuperação de senha
	 * @param type $result 
	 * @return type
	 */
	public static function validForgotDecrypt($code)
	{

		$code = base64_decode($code);
		
		$cryp = mb_substr($code, openssl_cipher_iv_length(User::CIFRA), null, '8bit');
		
		$IV = mb_substr($code, 0, openssl_cipher_iv_length(User::CIFRA), '8bit');
		
		$idrecovery = openssl_decrypt($cryp, User::CIFRA, User::SECRET, OPENSSL_RAW_DATA, $IV);

		$sql = new Sql();

		$r = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a INNER JOIN tb_users b USING(iduser) INNER JOIN tb_persons c USING(idperson) WHERE a.idrecovery = :idrecovery AND a.dtrecovery IS NULL AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
			":idrecovery"=>$idrecovery
		));

		if (count($r) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		} else {

			return $r[0];
		}
	}

	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery;", array(
			":idrecovery"=>$idrecovery
		));
	}

	public function setPassword($password)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser;", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		)); 
	}
}
 ?>