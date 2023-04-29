<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca;

class Soap
{


    public function send($acao,$xml,$certificado,$senha,$ambiente='homologacao'){
        switch ($ambiente)
        {
            case 'homologacao':
                $endpoint = 'https://notacariocahom.rio.gov.br/WSNacional/nfse.asmx';
                break;
            case 'producao':
                $endpoint = 'https://notacarioca.rio.gov.br/WSNacional/nfse.asmx';
                break;
        }
        switch ($acao){
            case 'Consultar':
                $action = 'http://notacarioca.rio.gov.br/ConsultarNfse';
                break;
        }

        $msgSize = strlen($xml);
        $headers = ['Content-Type: text/xml;charset=UTF-8', "SOAPAction: \"$action\"", "Content-length: $msgSize"];

        //Configuracao do Curl
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, $endpoint);
        curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 120 + 20);
        curl_setopt($oCurl, CURLOPT_HEADER, 1);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);

        $data = file_get_contents($certificado);
        $certPassword = $senha;

        openssl_pkcs12_read($data, $certs, $certPassword);
        $err = openssl_error_string();
        if ($err) {
            throw new \Exception("Error while validating certificate and password: " . $err);
        }
        $pkey = $certs['pkey'];

        $pemPath = sys_get_temp_dir() . '/' . uniqid() . '.pem';
        file_put_contents($pemPath, $certs['cert'] . $pkey);

        curl_setopt($oCurl, CURLOPT_SSLVERSION, 0);
        curl_setopt($oCurl, CURLOPT_SSLCERT, $pemPath);
        // curl_setopt($oCurl, CURLOPT_SSLKEY, sys_get_temp_dir() . '/file.key'); // Not necessary because both CRT and KEY are on the same file

        // Use if encrypt needed
        // curl_setopt($oCurl, CURLOPT_KEYPASSWD, $encryptPassword);

        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($oCurl);
        $soapErr = curl_error($oCurl);

        $headSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
        curl_close($oCurl);

        // Remove .pem temp file
        unlink($pemPath);
        $responseHead = trim(substr($response, 0, $headSize));
        $responseBody = trim(substr($response, $headSize));

        if ('' != $soapErr) {
            throw new \Exception($soapErr . " [$endpoint]");
        }

        if (200 != $httpCode) {
            throw new \Exception("HTTP error code: [$httpCode] - [$endpoint] - " . $responseBody);
        }

        return $responseBody;



    }
}