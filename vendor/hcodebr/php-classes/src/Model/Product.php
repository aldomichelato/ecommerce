<?php

namespace Hcode\Model;

use \Hcode\Db\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


class Product extends Model{

	public static function listAll()
	{
		$sql = new Sql();

        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}	

    public static function checkList($list)
    {

        foreach($list as &$row){

            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();

        }

        return $list;

    }

	public function save(){

		$sql = new Sql();

        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));

        $this->setData($results[0]);

	}

	public function get($idproduct)
	{

		$sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$idproduct
        ]);

        $this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$this->getidproduct()
        ]);

	}

    public function checkPhoto()
    {

        if (file_exists(  //verifica arquivo na pasta
            $_SERVER['DOCUMENT_ROOT'] 
                        . DIRECTORY_SEPARATOR . 
            "res"       . DIRECTORY_SEPARATOR . 
            "site"      . DIRECTORY_SEPARATOR . 
            "img"       . DIRECTORY_SEPARATOR . 
            "products"  . DIRECTORY_SEPARATOR . 
            $this->getidproduct() . ".jpg" //id do produto
        )) {  

            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg"; //se a foto existir retorna

        } else {

            $url = "/res/site/img/product.jpg"; // se não existir retorna a imagem cinza

        }

        return $this->setdesphoto($url);

    }

    public function getValues()
    {
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    public function setPhoto($file)
    {
        /* Identificar imagem criada pelo usuário, convertê-la para jpg */
        /* 
         * explode: vao procurar o ". (ponto)" no arquivo converterá para array
         * end: deve pegar apenas o que vier após o ponto.
         */
        $extension = explode('.', $file['name']);
        $extension = end($extension);

        var_dump($extension);
        switch ($extension) {
            case "jpg":
            case "jpeg":
            $image = imagecreatefromjpeg($file["tmp_name"]);
            break;
            
            case "gif":
            $image = imagecreatefromgif($file["tmp_name"]);
            break;
            case "png":
            $image = imagecreatefrompng($file["tmp_name"]);
            break;
        }
    
       $dist = $_SERVER['DOCUMENT_ROOT'] 
                        . DIRECTORY_SEPARATOR . 
            "res"       . DIRECTORY_SEPARATOR . 
            "site"      . DIRECTORY_SEPARATOR . 
            "img"       . DIRECTORY_SEPARATOR . 
            "products"  . DIRECTORY_SEPARATOR . 
            $this->getidproduct() . ".jpg";
            imagejpeg($image, $dist);
            /* Destruindo a imagem */
            imagedestroy($image);
            /* Carregando a imagem na memoria */
            $this->checkPhoto();

    }

    public function getFromURL($desurl)
    {
        $sql = new Sql();
        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl", [
            ':desurl'=>$desurl
        ]);
        $this->setData($rows[0]);
    }

    public function getCategories()
    {
        $sql = new Sql();
        return $sql->select("
            SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct
        ", [
            ':idproduct'=>$this->getidproduct()
        ]);
    }


	
}
				

?>