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
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserMsgSuccess";
	
	/**
	 * 
	 * @return type
	 */
	public static function getFromSession()
	{
		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
		{
			$user->setData($_SESSION[User::SESSION]);
		}

		return $user;
	}


	/**
	 * Valida login
	 * @param type $login 
	 * @param type $password 
	 * @return type
	 */
	public static function login($login, $password)
	{
		$sql = new Sql();
		
		$r = $sql->select("
			SELECT * 
			FROM tb_users a 
			INNER JOIN tb_persons b ON a.idperson = b.idperson 
			WHERE a.deslogin = :LOGIN", [
		     ":LOGIN"=>$login
		]);

		if (count($r) === 0) 
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $r[0];
		

		if (password_verify($password, $data["despassword"]) === true) 
		{
			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();
			
			return $user;

		} 
		else 
		{
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
		if (!User::checkLogin($inadmin)) 
		{
			if ($inadmin) 
			{
				header("Location: /admin/login");
			} 
			else 
			{
				header("Location: /login");
			}
			exit;
		} 
	}

	/**
	 * Verifica se usuário está logado
	 * @return type
	 */
	public static function checkLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//Não está logado
			return false;
		}
		else
		{
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) 
			{
				return true;
			}
			else if ($inadmin === false) 
			{
				return true;
			}
			else 
			{
				return false;
			}
		}
	}


	/**
	 * Verifica existencia do login no db
	 * @param type $login 
	 * @param type $password 
	 * @return type
	 */
	public static function checkLoginExist($login)
	{
		$sql = new Sql();
		
		$r = $sql->select("
			SELECT * 
			FROM tb_users 
			WHERE deslogin = :LOGIN", [
		     ":LOGIN"=>$login
		]);

		return (count($r) > 0);
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

		return $sql->select("
			SELECT * 
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson;");
	}

	/**
	 * Salva novo registro de usuario no banco
	 * @return type
	 */
	public function save() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_users_save(:DESPERSON, :DESLOGIN, :DESPASSWORD, :DESEMAIL, :NRPHONE, :INADMIN)", [
			":DESPERSON"=>utf8_decode($this->getdesperson()),
			":DESLOGIN"=>$this->getdeslogin(),
			":DESPASSWORD"=>User::getPasswordHash($this->getdespassword()),
			":DESEMAIL"=>$this->getdesemail(),
			":NRPHONE"=>$this->getnrphone(),
			":INADMIN"=>$this->getinadmin()
		]);

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

		$r = $sql->select("
			SELECT * 
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			WHERE a.iduser = :IDUSER", [
			":IDUSER"=>$iduser
		]);

		$data = $r[0];

		$data['desperson'] = utf8_encode($data['desperson']);

		$this->setData($data);
	}

	/**
	 * Salva alteração no registro de um usuário no banco
	 * @return type
	 */
	public function update() 
	{
		$sql = new Sql();

		$r = $sql->select("CALL sp_usersupdate_save(:IDUSER, :DESPERSON, :DESLOGIN, :DESPASSWORD, :DESEMAIL, :NRPHONE, :INADMIN)", [
			":IDUSER"=>$this->getiduser(),
			":DESPERSON"=>utf8_decode($this->getdesperson()),
			":DESLOGIN"=>$this->getdeslogin(),
			":DESPASSWORD"=>User::getPasswordHash($this->getdespassword()),
			":DESEMAIL"=>$this->getdesemail(),
			":NRPHONE"=>$this->getnrphone(),
			":INADMIN"=>$this->getinadmin()
		]);

		$this->setData($r[0]);
	}

	/**
	 * Apaga registro de um usuário no banco
	 * @return type
	 */
	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:IDUSER)", [
			":IDUSER"=>$this->getiduser()
		]);
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

		$r = $sql->select("
			SELECT * 
			FROM tb_persons a 
			INNER JOIN tb_users b USING(idperson) 
			WHERE a.desemail = :EMAIL;", [
			":EMAIL"=>$email
		]);

		if (count($r) === 0) 
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		} 
		else 
		{
			$data = $r[0];

			$r2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:IDUSER, :DESIP)", [
				":IDUSER"=>$data["iduser"],
				":DESIP"=>$_SERVER["REMOTE_ADDR"]
			]);

			if (count($r2) === 0) 
			{
				throw new \Exception("Não foi possível recuperar a senha.");
			} 
			else 
			{	
				$dataRecovery = $r2[0];

				$IV = random_bytes(openssl_cipher_iv_length(User::CIFRA));
				
				$cryp = openssl_encrypt($dataRecovery['idrecovery'], User::CIFRA, User::SECRET, OPENSSL_RAW_DATA, $IV);

				$code = base64_encode($IV.$cryp);

				if ($inadmin === true) 
				{
					$link = "http://ecommerce.com/admin/forgot/reset?code=$code";
				} 
				else 
				{
					$link = "http://ecommerce.com/forgot/reset?code=$code";
				} 

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", [
					"name"=>$data['desperson'],
					"link"=>$link
				]); 

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

		$r = $sql->select("
			SELECT * 
			FROM tb_userspasswordsrecoveries a 
			INNER JOIN tb_users b USING(iduser) 
			INNER JOIN tb_persons c USING(idperson) 
			WHERE a.idrecovery = :IDRECOVERY 
			AND a.dtrecovery IS NULL 
			AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", [
			":IDRECOVERY"=>$idrecovery
		]);

		if (count($r) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		} 
		else 
		{
			return $r[0];
		}
	}

	/**
	 * Atualiza o uso do código de recuperação no banco
	 * @param type $idrecovery 
	 * @return type
	 */
	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();

		$sql->query("
			UPDATE tb_userspasswordsrecoveries 
			SET dtrecovery = NOW() 
			WHERE idrecovery = :IDRECOVERY;", [
			":IDRECOVERY"=>$idrecovery
		]);
	}

	/**
	 * Altera senha no banco
	 * @param type $password 
	 * @return type
	 */
	public function setPassword($password)
	{
		$sql = new Sql();

		$sql->query("
			UPDATE tb_users 
			SET despassword = :PASSWORD 
			WHERE iduser = :IDUSER;", [
			":PASSWORD"=>$password,
			":IDUSER"=>$this->getiduser()
		]); 
	}

	/**
	 * Realiza o Hash code da senha passada
	 * @param type $password 
	 * @return type
	 */
	public static function getPasswordHash($password)
	{

		$ops = [
			"cost"=>10
		];

		return password_hash($password, PASSWORD_DEFAULT, $ops);

	}

	/**
	 * Altera mensagem de erro da constante
	 * @param type $msg 
	 * @return type
	 */
	public static function setMsgError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	/**
	 * Retorna mensagem de erro que está na constante
	 * @return type
	 */
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";

		User::clearMsgError();

		return $msg;
	}

	/**
	 * Apaga mensagem de erro da constante
	 * @return type
	 */
	public static function clearMsgError()
	{
		
		$_SESSION[User::ERROR] = NULL;
	
	}

	/**
	 * Altera mensagem de erro na constante de registro de usuário
	 * @param type $msg 
	 * @return type
	 */
	public static function setErrorRegister($msg)
	{
	
		$_SESSION[User::ERROR_REGISTER] = $msg;
	
	}

	/**
	 * Retorna mensagem de erro que está na constante de registro de usuário
	 * @return type
	 */
	public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ?$_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;
	}

	/**
	 * Apaga mensagem de erro da constante de registro de usuário
	 * @return type
	 */
	public static function clearErrorRegister() 
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}

	/**
	 * Altera mensagem de sucesso da constante
	 * @param type $msg 
	 * @return type
	 */
	public static function setMsgSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;

	}

	/**
	 * Retorna mensagem de sucesso que está na constante
	 * @return type
	 */
	public static function getMsgSuccess()
	{
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : "";

		User::clearMsgSuccess();

		return $msg;
	}

	/**
	 * Apaga mensagem de sucesso da constante
	 * @return type
	 */
	public static function clearMsgSuccess()
	{
		
		$_SESSION[User::SUCCESS] = NULL;
	
	}

	public function getOrders()
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
			WHERE a.iduser = :IDUSER", [
				":IDUSER"=>$this->getiduser()
			]);

		return $r;
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
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson
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
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			WHERE b.desperson LIKE :SEARCH 
			OR b.desemail = :SEARCH
			OR a.deslogin LIKE :SEARCH 
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;", [
				":SEARCH"=>"%" . $search . "%"
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