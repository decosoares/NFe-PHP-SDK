<?php

namespace WebmaniaBR;

class NFe {

    function __construct( array $vars ){

        $this->consumerKey = $vars['consumer_key'];
        $this->consumerSecret = $vars['consumer_secret'];
        $this->accessToken = $vars['access_token'];
        $this->accessTokenSecret = $vars['access_token_secret'];

    }

    function statusSefaz( $data = null ){

        $data = array();
        $response = self::connectWebmaniaBR( 'GET', 'https://webmaniabr.com/api/1/nfe/sefaz/', $data );
        if (isset($response->error)) return $response;
        if ($response->status == 'online') return true;
        else return false;

    }

    function validadeCertificado( $data = null ){

        $data = array();
        $response = self::connectWebmaniaBR( 'GET', 'https://webmaniabr.com/api/1/nfe/certificado/', $data );
        if (isset($response->error)) return $response;
        return $response->expiration;

    }

    function emissaoNotaFiscal( array $data ){

        $response = self::connectWebmaniaBR( 'POST', 'https://webmaniabr.com/api/1/nfe/emissao/', $data );
        return $response;

    }

    function consultaNotaFiscal( $chave ){

        $data = array();
        $data['chave'] = $chave;
        $response = self::connectWebmaniaBR( 'GET', 'https://webmaniabr.com/api/1/nfe/consulta/', $data );
        return $response;

    }

    function cancelarNotaFiscal( $chave, $motivo ){

        $data = array();
        $data['chave'] = $chave;
        $data['motivo'] = $motivo;
        $response = self::connectWebmaniaBR( 'PUT', 'https://webmaniabr.com/api/1/nfe/cancelar/', $data );
        return $response;

    }

    function inutilizarNumeracao( $sequencia, $motivo, $ambiente ){

        $data = array();
        $data['sequencia'] = $sequencia;
        $data['motivo'] = $motivo;
        $data['ambiente'] = $ambiente;
        $response = self::connectWebmaniaBR( 'PUT', 'https://webmaniabr.com/api/1/nfe/inutilizar/', $data );
        return $response;

    }

    function cartaCorrecao( $chave, $correcao ){

        $data = array();
        $data['chave'] = $chave;
        $data['correcao'] = $correcao;
        $response = self::connectWebmaniaBR( 'POST', 'https://webmaniabr.com/api/1/nfe/cartacorrecao/', $data );
        return $response;

    }

    function devolucaoNotaFiscal( $chave, $natureza_operacao, $ambiente, $codigo_cfop = null, $classe_imposto = null, $produtos = null ){

        $data = array();
        $data['chave'] = $chave;
        $data['natureza_operacao'] = $natureza_operacao;
        $data['ambiente'] = $ambiente;
        $data['codigo_cfop'] = $codigo_cfop;
        $data['classe_imposto'] = $classe_imposto;
        $data['produtos'] = $produtos;
        $response = self::connectWebmaniaBR( 'POST', 'https://webmaniabr.com/api/1/nfe/devolucao/', $data );
        return $response;

    }

    function ajusteNotaFiscal( $data ){
        $response = self::connectWebmaniaBR( 'POST', 'https://webmaniabr.com/api/1/nfe/ajuste/', $data );
        return $response;
    }

    function complementarNotaFiscal( $data ) {
        $response = self::connectWebmaniaBR( 'POST', 'https://webmaniabr.com/api/1/nfe/nfe_referenciada/', $data);
        return $response;
    }

    function connectWebmaniaBR( $request, $endpoint, $data ){

        // Set limits
        @set_time_limit( 300 );
        ini_set('max_execution_time', 300);
        ini_set('max_input_time', 300);
        ini_set('memory_limit', '256M');
        if (
            strpos($endpoint, '/sefaz/') !== false ||
            strpos($endpoint, '/certificado/') !== false
        ){
            $timeout = 5;
        } else {
            $timeout = 300;
        }

        // Header
        $headers = array(
          'Cache-Control: no-cache',
          'Content-Type:application/json',
          'X-Consumer-Key: '.$this->consumerKey,
          'X-Consumer-Secret: '.$this->consumerSecret,
          'X-Access-Token: '.$this->accessToken,
          'X-Access-Token-Secret: '.$this->accessTokenSecret
        );

        // Init connection
        $rest = curl_init();
        curl_setopt($rest, CURLOPT_CONNECTTIMEOUT , $timeout);
        curl_setopt($rest, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($rest, CURLOPT_URL, $endpoint.'?time='.time());
        curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rest, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($rest, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($rest, CURLOPT_CUSTOMREQUEST, $request);
        curl_setopt($rest, CURLOPT_POSTFIELDS, json_encode( $data ));
        curl_setopt($rest, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($rest, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($rest, CURLOPT_FOLLOWLOCATION, 1);

        // Connect to API
        $response = curl_exec($rest);
        $http_status = curl_getinfo($rest, CURLINFO_HTTP_CODE);
        $curl_errno = (int) curl_errno($rest);
        if ($curl_errno){
            $curl_strerror = curl_strerror($curl_errno);
        }
        curl_close($rest);

        // Get cURL errors
        $curl_error = new StdClass;
        if ($curl_errno){
          if (!$http_status){
            $curl_error->error = 'Não foi possível obter conexão na API da WebmaniaBR®, possível relação com bloqueio no Firewall ou versão antiga do PHP. Verifique junto ao seu programador e hospedagem a comunicação na URL: https://webmaniabr.com/api/. (cURL: '.$curl_strerror.' | PHP: '.phpversion().')';
          } else {
            $curl_error->error = 'Não foi possível se conectar na API da WebmaniaBR®. Tente novamente ou mais tarde. (cURL: '.$curl_strerror.' | HTTP Code: '.$http_status.')';
          }
        }

        // Return
        if ($curl_error){
            return $curl_error;
        } else {
            return json_decode($response);
        }

    }

}

?>
