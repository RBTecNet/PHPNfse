<?php

use Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Cancelar\CancelarNfse;

require "vendor/autoload.php";
//rotina de teste de consulta

$ambiente = "homologacao";
$certificado = "c:/certificados/telefonia.pfx";
$senha = "Brasinorte155";


$dados = [
    'IdentificacaoNfse' => [
        'Numero' => '202300000044653',
        'CpfCnpj'=>[
            'Cnpj' => '18890963000173',
        ],
        'InscricaoMunicipal' => '12717179',
        'CodigoMunicipio' => '3305505',
    ],
    'CodigoCancelamento' => '2',
];

$operacao = new CancelarNfse();
$retorno = $operacao->CancelarNfse($ambiente,$dados,$certificado,$senha);
var_dump($retorno);