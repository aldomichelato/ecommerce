<?php

namespace Hcode;

use Rain\Tpl;

class Page{

    private $tpl; //private outras classes nao tem acesso
    private $options = [];
    private $defaults = [
        "data"=>[]

    ];

    public function __construct($opts = array()){
        
        $this->options = array_merge($this->defaults, $opts); //

        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false
        );

        Tpl::configure( $config );

        $this->tpl = new Tpl;

        $this->setData($this->options["data"]);

        $this->tpl->draw("header"); //chama na tela

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
        
        $this->tpl->draw("footer");
    }


}



?>