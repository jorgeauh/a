<?php
/*error_reporting(E_ALL);
ini_set('display_errors', '1');*/

require_once 'lib/PayU.php';


/**
* 
*/
class PayUSDK
{
	

	//Credenciales
	/**
	 * The account Id
	 */
	var $accountID 	= '12345';
	/**
	 * The merchant API key
	 */
	var $apiKey		= "7dg9ao36ro3jetgr1gkbtjb8s3"; //Ingrese aquí su propio apiKey.
	/**
	 * The merchant API Login
	 */
	var $apiLogin	= "5fc4e7484ad3a77"; //Ingrese aquí su propio apiLogin.
	/**
	 * The merchant Id
	 */
	var $merchantId	= "12345"; //Ingrese aquí su Id de Comercio.

	
	function __construct($isCashPayment = false, $credentials = array())
	{

		PayU::$language = SupportedLanguages::ES; //Seleccione el idioma.
		
		if(sizeof($credentials) > 0){
			if(isset($credentials['accountID'])){
				$this->setAccountID($credentials['accountID']);
			}
			if(isset($credentials['apiKey'])){
				$this->setApiKey($credentials['apiKey']);
			}
			if(isset($credentials['apiLogin'])){
				$this->setApiLogin($credentials['apiLogin']);
			}
			if(isset($credentials['merchantId'])){
				$this->setMerchantId($credentials['merchantId']);
			}
		}

		
		// URL de Pagos
		Environment::setPaymentsCustomUrl("https://api.payulatam.com/payments-api/4.0/service.cgi"); 
		// URL de Consultas
		Environment::setReportsCustomUrl("https://api.payulatam.com/reports-api/4.0/service.cgi"); 
		// URL de Suscripciones para Pagos Recurrentes
		Environment::setSubscriptionsCustomUrl("https://api.payulatam.com/payments-api/rest/v4.3/"); 

		//Informacion para conexion de SDK
		PayU::$apiKey 		= $this->getApiKey();
		PayU::$apiLogin 	= $this->getApiLogin();
		PayU::$merchantId 	= $this->getMerchantId();						

		
	}

	public function getAccountID(){
		return $this->accountID;
	}

	public function setAccountID($accountID){
		$this->accountID = $accountID;
	}

	public function getApiLogin(){
		return $this->apiLogin;
	}

	public function setApiLogin($apiLogin){
		$this->apiLogin = $apiLogin;
	}

	public function getApiKey(){
		return $this->apiKey;
	}

	public function setApiKey($apiKey){
		$this->apiKey = $apiKey;
	}

	public function getMerchantId(){
		return $this->merchantId;
	}

	public function setMerchantId($merchantId){
		$this->merchantId = $merchantId;
	}
}
