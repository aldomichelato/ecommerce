<?php

namespace Hcode;

class PageAdmin extends Page{

    public function __construct($opts = array(), $tpl_dir = "/views/admin/")
    {
        
        parent::__construct($opts, $tpl_dir); //metodo parent faz referencia a classe pai que foi herdadam no caso a Page
    
    }

}

?>