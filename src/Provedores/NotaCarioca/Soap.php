<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

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
            case 'Cancelar':
                $action = 'http://notacarioca.rio.gov.br/CancelarNfse';
                break;
            case 'GerarNfse':
                $action = 'http://notacarioca.rio.gov.br/GerarNfse';
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
        $res =  $this->extractContentFromResponse($responseBody);

        $status = $this->isSuccess($res);
        if ($status) {
            switch ($acao) {
                case 'GerarNfse':
                    $resultado = $this->formatGerarSuccessResponse($res);
                    break;
                case 'Consultar':
                    $resultado = $this->formatConsultaSuccessResponse($res);
                    break;
                case 'Cancelar':
                    $resultado = $this->formatCancelarSuccessResponse($res);
                    break;

            }
            return $resultado;
        }else{
            return $this->getErrors($res);
        }

    }
    protected function extractContentFromResponse(string $response): string
    {
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadXML($response);

        if (!empty($dom->getElementsByTagName('outputXML')->item(0))) {
            $node = $dom->getElementsByTagName('outputXML')->item(0);

            return $node->textContent;
        }

        return $response;
    }
    public function isSuccess(string $responseXml): bool
    {
        $encode = new XmlEncoder();
        $resultArr = $encode->decode($responseXml, '');
        return !isset($resultArr['ListaMensagemRetorno']) ? true : false;
    }
    public function getErrors(string $responseXml): array
    {
        $encode = new XmlEncoder();
        $resultArr = $encode->decode($responseXml, '');

        if (isset($resultArr['ListaMensagemRetorno'])) {
            if (isset($resultArr['ListaMensagemRetorno']['MensagemRetorno']['Codigo'])) {
                $errors[] = $resultArr['ListaMensagemRetorno']['MensagemRetorno']['Codigo'].' - '.$resultArr['ListaMensagemRetorno']['MensagemRetorno']['Mensagem'];
            } else {
                foreach ($resultArr['ListaMensagemRetorno']['MensagemRetorno'] as $msgRetorno) {
                    $errors[] = $msgRetorno['Codigo'].' - '.$msgRetorno['Mensagem'];
                }
            }

            return $errors;
        }

        return [];
    }
    public function formatConsultaSuccessResponse(string $responseXml)
        {
            $encode = new XmlEncoder();
            $resultArr = $encode->decode($responseXml, '');
            $responseArr = [];
            if (isset($resultArr['ListaNfse']) and isset($resultArr['ListaNfse']['CompNfse'])) {
                $temp = $resultArr['ListaNfse']['CompNfse'];
                unset($temp['NfseCancelamento']);
                $countResult = count($temp);
                if ($countResult <= 1) {
                    if (isset($resultArr['ListaNfse']['CompNfse']['NfseCancelamento'])){
                        $resultArr['ListaNfse']['CompNfse']['Nfse']['InfNfse']['Status']='Cancelado';
                        $resultArr['ListaNfse']['CompNfse']['Nfse']['InfNfse']['DataCancelamento']=$resultArr['ListaNfse']['CompNfse']['NfseCancelamento']['Confirmacao']['DataHoraCancelamento'];

                    }else{
                        $resultArr['ListaNfse']['CompNfse']['Nfse']['InfNfse']['Status']='Normal';
                        $resultArr['ListaNfse']['CompNfse']['Nfse']['InfNfse']['DataCancelamento']='0000-00-00';
                    }
                    $responseArr[] = $resultArr['ListaNfse']['CompNfse']['Nfse']['InfNfse'];
                } else {
                    foreach ($resultArr['ListaNfse']['CompNfse'] as $nfse) {
                        if (isset($nfse['NfseCancelamento'])){
                            $nfse['Nfse']['InfNfse']['Status']='Cancelado';
                            $nfse['Nfse']['InfNfse']['DataCancelamento']=$nfse['NfseCancelamento']['Confirmacao']['DataHoraCancelamento'];
                        }
                        else{
                            $nfse['Nfse']['InfNfse']['Status']='Normal';
                            $nfse['Nfse']['InfNfse']['DataCancelamento']='0000-00-00';
                        }
                        $responseArr[] = $nfse['Nfse']['InfNfse'];
                    }
                }
            }
            $i=0;
           foreach ($responseArr as $nfs) {
                $doc = $nfs['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cnpj'];
                if (is_null($doc)){
                    $doc = $nfs['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cpf'];
                }
                try {
                    $numerorps = $nfs['IdentificacaoRps']['Numero'];
                } catch (\Exception $e) {
                    $numerorps = "";
                }
                $resultado[$i] = [
                    'CNPJ' => $doc,
                    'RAZAOSOCIAL' => $nfs['TomadorServico']['RazaoSocial'],
                    'Numero' => $nfs['Numero'],
                    'Autorizacao' => $nfs['CodigoVerificacao'],
                    'DataEmissao' => $nfs['DataEmissao'],
                    'NumeroRps' => $numerorps,
                    'Status' => $nfs['Status'],
                    'DataCancelamento' =>$nfs['DataCancelamento'],


                ];
                $i++;
            }
           return $resultado;
        }
    public function formatGerarSuccessResponse(string $responseXml)
    {
        $encode = new XmlEncoder();
        $resultArr = $encode->decode($responseXml, '');
        $responseArr = [];
        if (isset($resultArr['CompNfse']) and isset($resultArr['CompNfse']['Nfse'])) {
            foreach ($resultArr['CompNfse'] as $nfse) {
                $responseArr[] = $nfse['InfNfse'];
            }
            $i = 0;
            foreach ($responseArr as $nfs) {
                $doc = $nfs['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cnpj'];
                if (is_null($doc)) {
                    $doc = $nfs['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cpf'];
                }
                try {
                    $numerorps = $nfs['IdentificacaoRps']['Numero'];
                } catch (\Exception $e) {
                    $numerorps = "";
                }
                $resultado = [
                    'CNPJ' => $doc,
                    'RAZAOSOCIAL' => $nfs['TomadorServico']['RazaoSocial'],
                    'Numero' => $nfs['Numero'],
                    'Autorizacao' => $nfs['CodigoVerificacao'],
                    'DataEmissao' => $nfs['DataEmissao'],
                    'NumeroRps' => $numerorps
                ];
                $i++;
            }
        }else{
            return $resultArr['ListaMensagemRetorno']['MensagemRetorno'];
        }
        return $resultado;
    }
    public function formatCancelarSuccessResponse(string $responseXml)
    {
        $encode = new XmlEncoder();
        $resultArr = $encode->decode($responseXml, '');
        //return $resultArr;
        $responseArr = [];
        if (isset($resultArr['Cancelamento']) and isset($resultArr['Cancelamento']['Confirmacao'])) {
            foreach ($resultArr['Cancelamento'] as $nfse) {
                $responseArr[] = $nfse;
            }
        } else {
            foreach ($resultArr['ListaMensagemRetorno'] as $nfse) {
                $responseArr[] = $nfse;
            }
        }
        return $responseArr;
        }

}