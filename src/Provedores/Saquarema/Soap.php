<?php

namespace Rbtecnet\Phpnfse\Provedores\Saquarema;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Soap
{
    public function send($acao,$xml,$certificado,$senha,$ambiente='homologacao'){
        switch ($ambiente)
        {
            case 'producao':
            case 'homologacao':
                $endpoint = 'http://sistemas.saquarema.rj.gov.br/NFSe.Portal.Integracao/Services.svc';
                break;
        }

        switch ($acao){
            case 'Consultar':
                $action = 'http://tempuri.org/INFSEConsultas/ConsultarNfse';
                break;
            case 'Cancelar':
                $action = 'http://tempuri.org/INFSEGeracao/CancelarNfse';
                break;
            case 'GerarNfse':
                $action = 'http://tempuri.org/INFSEGeracao/EnviarLoteRpsSincrono';
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
        $responseBody = trim(substr($response, $headSize));
        if ('' != $soapErr) {
            return $soapErr . " [$endpoint]";
        }
        if (200 != $httpCode) {
            return "HTTP error code: [$httpCode] - [$endpoint] - " . $responseBody;
        }
        $res =  $this->extractContentFromResponse($responseBody);
        $status = $this->isSuccess($res,$acao);
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
            return $this->getErrors($res,$acao);
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
    public function isSuccess(string $responseXml, $funcao='GerarNfse')
    {
        $encode = new XmlEncoder();
        if ($funcao=='GerarNfse') {
            $resultArr = $encode->decode($encode->decode($responseXml, '')['s:Body']['EnviarLoteRpsSincronoResponse']['EnviarLoteRpsSincronoResult'],'');
        }else if ($funcao=='Cancelar'){
            $resultArr = $encode->decode($encode->decode($responseXml, '')['s:Body']['CancelarNfseResponse']['CancelarNfseResult'],'');
        }
        return !isset($resultArr['ListaMensagemRetorno']) ? true : false;
    }
    public function getErrors(string $responseXml, $funcao='GerarNfse'): array
    {
        $encode = new XmlEncoder();
        if ($funcao=='GerarNfse'){
            $resultArr = $encode->decode($encode->decode($responseXml, '')['s:Body']['EnviarLoteRpsSincronoResponse']['EnviarLoteRpsSincronoResult'],'');
        }else if($funcao=='Cancelar'){
            $resultArr = $encode->decode($encode->decode($responseXml, '')['s:Body']['CancelarNfseResponse']['CancelarNfseResult'],'');
        }
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
            $resultArr = $encode->decode($responseXml, '')['s:Body']['ConsultarNfseResponse']['ConsultarNfseResult'];
            $resultArr = $encode->decode($resultArr, '');
            $responseArr = [];
            if (isset($resultArr['ListaNfse']) and isset($resultArr['ListaNfse']['CompNfse'])) {
                $countResult = count($resultArr['ListaNfse']['CompNfse']);
                if ($countResult <= 1) {
                    foreach ($resultArr['ListaNfse']['CompNfse'] as $nfse) {
                        $responseArr[] = $nfse['InfNfse'];
                    }
                } else {
                    foreach ($resultArr['ListaNfse']['CompNfse'] as $nfse) {
                        $responseArr[] = $nfse['Nfse']['InfNfse'];
                    }
                }
            }
           $i=0;
           foreach ($responseArr as $nfs) {
               $tomador = $nfs['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Tomador'];
                $doc = $tomador['IdentificacaoTomador']['CpfCnpj']['Cnpj'];
                if (is_null($doc)){
                    $doc = $tomador['IdentificacaoTomador']['CpfCnpj']['Cpf'];
                }
                $numerorps = $nfs['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Rps']['IdentificacaoRps']['Numero'];
                if (is_null($numerorps)){
                    $numerorps = "";
                }
                $resultado[$i] = [
                    'CNPJ' => $doc,
                    'RAZAOSOCIAL' => $tomador['RazaoSocial'],
                    'Numero' => $nfs['Numero'],
                    'Autorizacao' => $nfs['CodigoVerificacao'],
                    'DataEmissao' => $nfs['DataEmissao'],
                    'NumeroRps' => $numerorps
                ];
                $i++;
            }
           return $resultado;
        }
    public function formatGerarSuccessResponse(string $responseXml)
    {
        $encode = new XmlEncoder();
        $resultArr = $resultArr = $encode->decode($encode->decode($responseXml, '')['s:Body']['EnviarLoteRpsSincronoResponse']['EnviarLoteRpsSincronoResult'],'')['ListaNfse'];
        $responseArr = [];
        if (isset($resultArr['CompNfse']) and isset($resultArr['CompNfse']['Nfse'])) {
            foreach ($resultArr['CompNfse'] as $nfse) {
                $responseArr[] = $nfse['InfNfse'];
            }
            $i = 0;

            foreach ($responseArr as $nfs) {
                $tomador = $nfs['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Tomador'];
                $doc = $tomador['IdentificacaoTomador']['CpfCnpj']['Cnpj'];
                if (is_null($doc)) {
                    $doc = $tomador['IdentificacaoTomador']['CpfCnpj']['Cpf'];
                }
                try {
                    $numerorps = $nfs['Numero'];
                } catch (\Exception $e) {
                    $numerorps = "";
                }
                $resultado = [
                    'CNPJ' => $doc,
                    'RAZAOSOCIAL' => $tomador['RazaoSocial'],
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
        $resultArr = $resultArr = $encode->decode($encode->decode($responseXml, '')['s:Body']['CancelarNfseResponse']['CancelarNfseResult'],'')['RetCancelamento'];
        $responseArr = [];

        if (isset($resultArr['NfseCancelamento']) and isset($resultArr['NfseCancelamento']['Confirmacao'])) {
            foreach ($resultArr['NfseCancelamento'] as $nfse) {
                $responseArr[] = $nfse;
            }
        } else {
            foreach ($resultArr['ListaMensagemRetorno'] as $nfse) {
                $responseArr[] = $nfse;
            }
        }
        return $responseArr;
        }
    public function gravaarquivo($arquivo, $conteudo){
        $file = fopen($arquivo, "w");
        fwrite($file, $conteudo);
        fclose($file);
    }
}