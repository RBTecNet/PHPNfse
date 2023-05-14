<?php

namespace Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Download;

class DownloadDanfeNfse
{
    public function DownloadDanfeNfse($notafiscal, $codver, $cnpj, $type='pdf'){
        try{
            $codbar = $notafiscal.$codver.$cnpj;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,"http://sistemas.saquarema.rj.gov.br/NFSe.Portal/AutenticidadeNota/ConsultarPorCodigoBarras");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,"CodigoBarras=".$codbar);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec ($ch);
            curl_close ($ch);
            $json = json_decode($server_output,true);
            if($type=='pdf'){
                $urldanfe = "http://sistemas.saquarema.rj.gov.br/NFSe.Portal/Prestador/Nota/DownloadPDF/?notas=".$json['IdentificadorCriptografado'];
            }else{
                $urldanfe = "http://sistemas.saquarema.rj.gov.br/NFSe.Portal/Prestador/Nota/DownloadXML/?notaXML=".$json['IdentificadorCriptografado'];
            }
            //header('location:'.$urldanfe);
            //die();
            return $urldanfe;
        }catch (\Exception $e){
            alert($e->getMessage());
        }
    }
}