<?php

namespace Hcode\Model;

use \Hcode\Db\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSuccess";


	public static function getFromSession()
	{
		$user = new User();

		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){ // verifica se a sessao existe o i i user eh maior que zero

			$user->setData($_SESSION[User::SESSION]); // carrega o usuario

		}

		return $user;

	}


	public static function checkLogin($inadmin = true) //metodo que verifica se o usuario esta logado
	{
		if(
			!isset($_SESSION[User::SESSION]) //vefica se a session do usuario não for definida
			||
			!$_SESSION[User::SESSION]  //se ela for falsa
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0//se o id nao for maior que 0 é um usuario
			){

			//não esta logado
			return false;

		}else{
			//esse if só acontece se ele tentar acessar uma rota de administrador
			if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true){

				return true;

			} else if($inadmin === false){ //esta logado 

				return true;

			} else {

				return false;
			}

		}

	}

	public static function login($login, $password)
	{

		$sql = new Sql();
        
		/*$results = $sql->select("SELECT * FROM tb_users where deslogin = :LOGIN", array(
			":LOGIN"=>$login
	    ));*/

	     $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

		if(count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida 01.");
		}

		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true)
		{

			$user = new User();
			
			$user->setData($data);

			$_SESSION[User::SESSION] = $user ->getValues();

			return $user;

		}else{
			throw new \Exception("Usuário inexistente ou senha inválida 02.");
		}
	}


	public static function verifyLogin($inadmin = true){

        if (!User::checkLogin($inadmin)) {

            if ($inadmin) {

                header("Location: /admin/login");

            } else {

                header("Location: /login");

            }

            exit;

        }

    }

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function listAll()
	{

		$sql = new sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY desperson");

	}

	public function save() { //Cria um novo usuário no Banco de Dados
        $sql = new Sql();
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :desinadmin)",
        						 //CALL `db_ecommerce`.`sp_users_save`('1', 'aldo', 'admin', 'aldo@uol.com', 35232087, 1);	
        array(
            ":desperson" => utf8_encode($this->getdesperson()),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => User::getPasswordHash($this->getdespassword()),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":desinadmin" => $this->getinadmin()
        ));
        $data = $results[0];
        $this->setData($data);
    }

	
	public function get($iduser)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$this->setData($results[0]);

	}

	public function update()
	{

		$sql = new Sql();
		
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),  //esses get foram gerados pelo getData
			":deslogin"=>$this->getdeslogin(), 
			":despassword"=>$this->getdespassword(), 
			":desemail"=>$this->getdesemail(),  
			":nrphone"=>$this->getnrphone(),  
			":inadmin"=>$this->getinadmin() 
		));

		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

	}

	public static function getForgot($email, $inadmin = true)
	{
	     $sql = new Sql();
	     $results = $sql->select("
	         SELECT *
	         FROM tb_persons a
	         INNER JOIN tb_users b USING(idperson)
	         WHERE a.desemail = :email;
	     ", array(
	         ":email"=>$email
	     ));
	     if (count($results) === 0)
	     {
	         throw new \Exception("Não foi possível recuperar a senha.");
	     }
	     else
	     {
	         $data = $results[0];
	         $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
	             ":iduser"=>$data['iduser'],
	             ":desip"=>$_SERVER['REMOTE_ADDR']
	         ));
	         if (count($results2) === 0)
	         {
	             throw new \Exception("Não foi possível recuperar a senha.");
	         }
	         else
	         {
	             $dataRecovery = $results2[0];
	             $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	             $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
	             $result = base64_encode($iv.$code);
	             if ($inadmin === true) {
	                 $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result"; //link da administração
	             } else {
	                 $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result"; // link do usuario comum
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

	 public static function validForgotDecrypt($code)
	 {
	     $result = base64_decode($code);
	     $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
	     $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;
	     $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
	     $sql = new Sql();
	     $results = $sql->select("
	         SELECT *
	         FROM tb_userspasswordsrecoveries a
	         INNER JOIN tb_users b USING(iduser)
	         INNER JOIN tb_persons c USING(idperson)
	         WHERE
	         a.idrecovery = :idrecovery
	         AND
	         a.dtrecovery IS NULL
	         AND
	         DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
	     ", array(
	         ":idrecovery"=>$idrecovery
	     ));
	     if (count($results) === 0)
	     {
	         throw new \Exception("Não foi possível recuperar a senha.");
	     }
	     else
	     {
	         return $results[0];
	     }
	 }

	 public static function setForgotUsed($idrecovery){

	    $sql = new Sql();

	    $sql -> query("UPDATE  tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
	    		":idrecovery"=>$idrecovery

	    ));

	 }

	 public function setPassword($password){

	    $sql = new Sql();

	    $sql -> query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
	    		"password"=>$password,
	    		"iduser"=>$this -> getiduser()
	    ));

	 }

	 public static function setError($msg) {
        $_SESSION[User::ERROR] = $msg;
     }

     public static function getError() {
         $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
         User::clearError();
         return $msg;
     }

     public static function clearError() {
         $_SESSION[User::ERROR] = NULL;
     }

     public static function setErrorRegister($msg) {
         $_SESSION[User::ERROR_REGISTER] = $msg;
     }

     public static function getErrorRegister() {
         $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
         User::clearErrorRegister();
         return $msg;
     }

     public static function clearErrorRegister() {
         $_SESSION[User::ERROR_REGISTER] = NULL;
     }

     public static function checkLoginExist($login) {
         $sql = new Sql();
         $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
             ':deslogin' => $login
         ]);
         return (count($results) > 0);
     }

     public static function getPasswordHash($password) {
         return password_hash($password, PASSWORD_DEFAULT, [
             'cost' => 12
         ]);
     }

     public static function setSuccess($msg) {
         $_SESSION[User::SUCCESS] = $msg;
     }
     
     public static function getSuccess() {
         $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
         User::clearSuccess();
         return $msg;
     }
     
      public static function clearSuccess() {
        $_SESSION[User::SUCCESS] = NULL;
   	 }


}

?>