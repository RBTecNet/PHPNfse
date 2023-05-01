<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Cancelar;

use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Soap;

class CancelarNfse
{
    function CancelarNfse($ambiente='homologacao', array $dados=[], $certificado='',$senha=''){
        try {
            $gx = new GerarXmlCancela();
            $xml = $gx->GerarXmlCancela($dados);
            $soap = new Soap();
            $retorno = $soap->send('Cancelar', $xml, $certificado, $senha, $ambiente);
            return $retorno;
        }catch (\Exception $ex){

        }
    }
}