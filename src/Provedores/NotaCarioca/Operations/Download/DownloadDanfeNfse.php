<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Download;

class DownloadDanfeNfse
{
    function DownloadDanfeNfse($ambiente='homologacao', $inscricaomunicipal='', $notafiscal='', $codver=''){
        $codver = str_replace('-','',$codver);
        if ($ambiente=='homologacao'){
            $baseurl="https://notacariocahom.rio.gov.br/contribuinte/notaprint.aspx?inscricao=$inscricaomunicipal&nf=$notafiscal&verificacao=$codver";
        }else{
            $baseurl="https://notacarioca.rio.gov.br/contribuinte/notaprint.aspx?inscricao=$inscricaomunicipal&nf=$notafiscal&verificacao=$codver";
        }
        return $baseurl;
    }
}