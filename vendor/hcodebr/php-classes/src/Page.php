<?php

namespace Hcode;

use Rain\Tpl;

class Page{

    private $tpl; //private outras classes nao tem acesso
    private $options = [];
    private $defaults = [
        "header"=>true, //cabeçalho é true (vai aparecer)
        "footer"=>true, //rodapé também
        "data"=>[]

    ];

    public function __construct($opts = array(), $tpl_dir = "/views/"){
        
        $this->options = array_merge($this->defaults, $opts); // array_merge - Combina um ou mais arrays

        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false
        );

        Tpl::configure( $config );

        $this->tpl = new Tpl;

        $this->setData($this->options["data"]);

        if($this->options["header"] === true) $this->tpl->draw("header"); //chama na tela

    }

    private function setData($data = array()){

        foreach($data as $key => $value){

            $this->tpl->assign($key, $value);
        }
    }

    public function setTpl($nome, $data = array(), $returbHTML = false){

        $this->setData($data);

        return $this->tpl->draw($nome, $returbHTML);

    }

    public function __destruct(){
        
        if($this->options["footer"] === true) $this->tpl->draw("footer"); //chama na tela
    }


}



?>