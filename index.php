<?php

use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Consultar\ConsultarNfse;

require "vendor/autoload.php";
try{
    $operacao = new ConsultarNfse();
    echo $operacao->ConsultarNfse();
}catch (Exception $e){
    dd($e);
}
