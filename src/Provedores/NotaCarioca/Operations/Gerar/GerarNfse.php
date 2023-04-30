<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Gerar;

use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Soap;

class GerarNfse
{
    function GerarNfse($ambiente='homologacao', array $dados=[], $certificado='',$senha=''){
        try{
            $gx = new GerarXmlGerar();
            $xml = $gx->GerarXmlGerar($dados);
            $soap = new Soap();
            $retorno = $soap->send('GerarNfse', $xml, $certificado, $senha, $ambiente);
            return $retorno;
        }catch (\Exception $ex){

        }

    }
}