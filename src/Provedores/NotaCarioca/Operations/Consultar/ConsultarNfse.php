<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Consultar;

use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Soap;

class ConsultarNfse
{
    function ConsultarNfse($ambiente='homologacao', array $dados=[], $certificado='',$senha=''){
        try{
            $gx = new GerarXmlConsulta();
            $xml =  $gx->GerarXmlConsulta($dados);
            $soap = new Soap();
            $retorno = $soap->send('Consultar',$xml,$certificado,$senha,$ambiente);
            return $retorno;
        }catch (\Exception $e){

        }


        }
}