<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {

	$sql = new Hcode\DB\Sql();
	$results = $sql->selecT("SELECT * FROM TB_USERS");

	ECHO json_encode($results);
    
	echo "OK talarica";

});

$app->run();

 ?>