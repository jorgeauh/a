<?php

$uuid= "91DA67A1-61EC-45C1-8F00-9DF035F810E7";
$rfc = "AAA010101AAA";
$clienteFace = new ClienteFace();


//echo print_r($clienteFace->emiteDocumento($rfc,$uuid),true);

class ClienteFace{
	private $wsURL;
	
	function __construct() {
       		$this->wsURL="http://200.53.162.59:8080/Face/FaceService?wsdl";
   	}
	
	function emiteDocumento($rfc,$documentoJson){
		$consultaDocumentoRequest = new ConsultaDocumentoRequest($rfc,$documentoJson);
		try {
			$client=new SoapClient($this->wsURL, array('trace' => 1,'connection_timeout'=>15));
		} catch (Exception $e) {
			error_log("Error creando SoapClient:" . $e->getMessage());
			return 'exception error';
		}

		try {
			$resultado=$client->__soapCall('ConsultaDocumento',array('parameters' => $consultaDocumentoRequest));
			return $resultado;
		}catch (Exception $e) {
			error_log("Error creando accesando PAC 3:" . $e->getMessage());
		   return 'exception error';
    	}
    }

	
	
}

class ConsultaDocumentoRequest{
	private $request;
	function __construct($rfc,$uuid) {
		$this->request = new Request($rfc,$uuid);
   }
}
class Request{
	private $rfc;
	private $uuid;
	function __construct($rfc,$uuid) {
		$this->rfc=$rfc;
		$this->uuid=$uuid;
   }
}

?>
