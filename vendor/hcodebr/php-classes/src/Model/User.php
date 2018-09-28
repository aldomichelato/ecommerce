<?php

namespace Hcode\Model;

use \Hcode\Db\Sql;
use \Hcode\Model;

class User extends Model{

	const SESSION = "User";

	public static function login($login, $password)
	{

		$sql = new Sql();
        
		$results = $sql->select("SELECT * FROM tb_users where deslogin = :LOGIN", array(
			":LOGIN"=>$login
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


	public static function verifyLogin($inadmin = true) //verifica s esta logado ou não
	{

		if(
			!isset($_SESSION[User::SESSION]) //vefica se a session não for definida
			||
			!$_SESSION[User::SESSION]  //se ela for falsa
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0//se o id nao for maior que 0 é um usuario
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin// verifica se é admin
			) {

			    header("Location: /admin/login");  //se ela nao for definida vai para a tela de login
			    exit;

			}
	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}


	}

?>