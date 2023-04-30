<?php
use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Gerar\GerarNfse;
require "vendor/autoload.php";
try {
    //rotina de teste de consulta

    $ambiente = "homologacao";
    $certificado = "c:/certificados/telefonia.pfx";
    $senha = "Brasinorte155";
    $dados = [];

    $operacao = new GerarNfse();
    $retorno = $operacao->GerarNfse($ambiente,$dados,$certificado,$senha);
    var_dump($retorno);




}catch (\Exception $ex){

}