<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

require_once 'sdk.php';
require_once 'log.php';

class Payment extends PayUSDK
{

	public function doPayment(PaymentParams $paymentParams, $type = 'creditcard'){
		//Verifica que se pueda conectar al gateway
		$doPing = $this->doPingPayU();

		if($doPing['code'] != 0001){
			return $doPing['message'];
		}


		if($type == 'creditcard'){
			//Realiza la tansaccion
			$doPayment = $this->doPaymentCreditCard($paymentParams);

			if($doPayment['code'] != 0001){
				return $doPayment['exception'];
			}
			else{
				return $doPayment['response'];
			}
		}

		if($type == 'cash'){
			//Realiza la tansaccion
			$doPayment = $this->doPaymentCash($paymentParams);

			if($doPayment['code'] != 0001){
				return $doPayment['exception'];
			}
			else{
				return $doPayment['response'];
			}

		}

		return $doPayment;

		
	}

	private function doPingPayU(){
		//Params
		$result = array();
		$code = 0001;
		
		//Action
		$response = PayUPayments::doPing();
		if($response->code != 'SUCCESS'){
			$code = 0000;			
		}

		//Result
		$result['code'] 	= $code;
		$result['message'] 	= str_replace('{OPERACION@OPERACION}', 'PayUPayments::doPing', $this->getMessage($code));

		return $result;
	}

	private function doPaymentCreditCard(PaymentParams $paymentParams){

		//Params
		$parameters = array(
			//Ingrese aquí el identificador de la cuenta.
			PayUParameters::ACCOUNT_ID => $this->getAccountID(),
			//Ingrese aquí el código de referencia.
			PayUParameters::REFERENCE_CODE => $paymentParams->getReference(),
			//Ingrese aquí la descripción.
			PayUParameters::DESCRIPTION => $paymentParams->getDescription(),
			
			// -- Valores --
			//Ingrese aquí el valor.        
			PayUParameters::VALUE => $paymentParams->getAmount(),
			//Ingrese aquí la moneda.
			PayUParameters::CURRENCY => $paymentParams->getCurrency(),
			/** The tax value. */
			PayUParameters::TAX_VALUE => $paymentParams->getTax(),
			/** The tax return base. */
			PayUParameters::TAX_RETURN_BASE => $paymentParams->getTaxBase(),
			
			// -- Comprador 
			//Ingrese aquí el nombre del comprador.
			PayUParameters::BUYER_NAME => $paymentParams->getBuyerName(),
			//Ingrese aquí el email del comprador.
			PayUParameters::BUYER_EMAIL => $paymentParams->getBuyerEmail(),
			//Ingrese aquí el teléfono de contacto del comprador.
			PayUParameters::BUYER_CONTACT_PHONE => $paymentParams->getBuyerPhone(),
			//Ingrese aquí el documento de contacto del comprador.
			//PayUParameters::BUYER_DNI => $paymentParams->getBuyerDni(),
			//Ingrese aquí la dirección del comprador.
			PayUParameters::BUYER_STREET => $paymentParams->getBuyerAddress1(),
			PayUParameters::BUYER_STREET_2 => $paymentParams->getBuyerAddress2(),
			PayUParameters::BUYER_CITY => $paymentParams->getBuyerCity(),
			PayUParameters::BUYER_STATE => $paymentParams->getBuyerState(),
			PayUParameters::BUYER_COUNTRY => $paymentParams->getBuyerCountry(),
			PayUParameters::BUYER_POSTAL_CODE => $paymentParams->getBuyerPostcode(),
			PayUParameters::BUYER_PHONE => $paymentParams->getBuyerPhone(),
			
			// -- pagador --
			//Ingrese aquí el nombre del pagador.
			PayUParameters::PAYER_NAME => $paymentParams->getPayerName(),
			//Ingrese aquí el email del pagador.
			PayUParameters::PAYER_EMAIL => $paymentParams->getPayerEmail(),
			//Ingrese aquí el teléfono de contacto del pagador.
			PayUParameters::PAYER_CONTACT_PHONE => $paymentParams->getPayerPhone(),
			//Ingrese aquí el documento de contacto del pagador.
			//PayUParameters::PAYER_DNI => $paymentParams->getPayerDni(),

			//Ingrese aquí la dirección del pagador.
			PayUParameters::PAYER_STREET => $paymentParams->getPayerAddress1(),
			PayUParameters::PAYER_STREET_2 => $paymentParams->getPayerAddress2(),
			PayUParameters::PAYER_CITY => $paymentParams->getPayerCity(),
			PayUParameters::PAYER_STATE => $paymentParams->getPayerState(),
			PayUParameters::PAYER_COUNTRY => $paymentParams->getPayerCountry(),
			PayUParameters::PAYER_POSTAL_CODE => $paymentParams->getPayerPostcode(),
			PayUParameters::PAYER_PHONE => $paymentParams->getPayerPhone(),
			PayUParameters::PAYER_BIRTHDATE => $paymentParams->getPayerBirthDate(),
			
			// -- Datos de la tarjeta de crédito -- 
			//Ingrese aquí el número de la tarjeta de crédito
			PayUParameters::CREDIT_CARD_NUMBER => $paymentParams->getCreditCardNumber(),
			//Ingrese aquí la fecha de vencimiento de la tarjeta de crédito
			PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $paymentParams->getCreditCardExpirationDate(),
			//Ingrese aquí el código de seguridad de la tarjeta de crédito
			PayUParameters::CREDIT_CARD_SECURITY_CODE=> $paymentParams->getCreditCardSecurityCode(),
			//Ingrese aquí el nombre de la tarjeta de crédito
			//PaymentMethods::VISA||PaymentMethods::MASTERCARD||PaymentMethods::AMEX    
			PayUParameters::PAYMENT_METHOD => $paymentParams->getPaymentMethod(),
			
			//Ingrese aquí el número de cuotas.
			PayUParameters::INSTALLMENTS_NUMBER => $paymentParams->getInstallmentsNumber(),
			//Ingrese aquí el nombre del pais.
			PayUParameters::COUNTRY => $paymentParams->getCountry(),
			
			//Session id del device.
			PayUParameters::DEVICE_SESSION_ID => $paymentParams->getDeviceSessionId(),
			//IP del pagadador
			PayUParameters::IP_ADDRESS => $paymentParams->getIpAddress(),
			//Cookie de la sesión actual.
			PayUParameters::PAYER_COOKIE=> $paymentParams->getPayerCookie(),
			//Cookie de la sesión actual.        
			PayUParameters::USER_AGENT=> $paymentParams->getUserAgent()
		);
		$result = array();
		$code = 0001;
		$rawResult = '';

		//Actions
		try {
			$response = PayUPayments::doAuthorizationAndCapture($parameters);
			$rawResult = $response;

			if ($response) {

				$result['code'] 	= $code;
				$result['message'] 	= str_replace('{OPERACION@OPERACION}', 'PayUPayments::doAuthorizationAndCapture', $this->getMessage($code));
				$result['response']['order'] 		= $response->transactionResponse->orderId;
				$result['response']['transaction'] 	= $response->transactionResponse->transactionId;	
				$result['response']['state'] 		= $response->transactionResponse->state;			

				switch ($response->transactionResponse->state) {
					case 'APPROVED':
						$status = 0003;
						break;
					case 'PENDING':
						$status = 0004;
						break;
					case 'REJECTED':
					case 'DECLINED':
						$status = 0006;
						break;
				}				
				
				$result['response']['stateText'] = str_replace('{STATUS@STATUS}',$response->transactionResponse->state,$this->getMessage($status));								

				if ($response->transactionResponse->state=="PENDING") {
					 $status = 0005;
					 $result['response']['pendingReason'] = str_replace('{RAZON@RAZON}',$response->transactionResponse->pendingReason,$this->getMessage($status));
				}

				if(isset($response->transactionResponse->paymentNetworkResponseCode)){
					$result['response']['paymentNetworkResponseCode'] = $response->transactionResponse->paymentNetworkResponseCode;
				}
				if(isset($response->transactionResponse->paymentNetworkResponseErrorMessage)){
					$result['response']['paymentNetworkResponseErrorMessage'] = $response->transactionResponse->paymentNetworkResponseErrorMessage;
				}
				if(isset($response->transactionResponse->trazabilityCode)){
					$result['response']['trazabilityCode'] = $response->transactionResponse->trazabilityCode;
				}
				if(isset($response->transactionResponse->authorizationCode)){
					$result['response']['authorizationCode'] = $response->transactionResponse->authorizationCode;
				}
				if(isset($response->transactionResponse->responseCode)){
					$result['response']['responseCode'] = $response->transactionResponse->responseCode;
				}
				if(isset($response->transactionResponse->responseMessage)){
					$result['response']['responseMessage'] = $response->transactionResponse->responseMessage;
				}

				
				$logdata['invoiceid'] 		= $paymentParams->getReference();
				$logdata['orderid'] 		= $result['response']['order'];
				$logdata['transaction']		= $result['response']['transaction'];
				$logdata['status'] 			= $result['response']['state'];
				$logdata['paymentmethod'] 	= $paymentParams->getPaymentMethod();
				$logdata['message']			= $result['message']." ".$result['response']['stateText'];
				$logdata['urlReceipt']		= ''; 
				$logdata['fecha']			= date("Y-m-d H:i:s");
				$logdata['expiracion']		= '0000-00-00 00:00:00';

				LogData::saveData($logdata);							
			} 
		} catch (Exception $e) {
			$code = 0000;
			$result['code'] 		= $code;
			$result['message'] 		= str_replace('{OPERACION@OPERACION}', 'PayUPayments::doAuthorizationAndCapture', $this->getMessage($code));
			$result['exception'] 	= $e->getMessage();
		}

		$notificacion = '';
		$notificacion .= "Parametros:\n";
		$notificacion .= json_encode($parameters);
		$notificacion .= "\n\n";
		$notificacion .= "Respuesta:\n";
		$notificacion .= json_encode($result);
		$notificacion .= "\n\n";
		$notificacion .= "Respuesta (RAW):\n";
		$notificacion .= json_encode($rawResult);

		//Result
		return $result;
	}

	private function doPaymentCash(PaymentParams $paymentParams){		

		$parameters = array(
			//Ingrese aquí el identificador de la cuenta.
			PayUParameters::ACCOUNT_ID => $this->getAccountID(),
			//Ingrese aquí el código de referencia.
			PayUParameters::REFERENCE_CODE => $paymentParams->getReference(),
			//Ingrese aquí la descripción.
			PayUParameters::DESCRIPTION => $paymentParams->getDescription(),
			
			// -- Valores --
			//Ingrese aquí el valor.        
			PayUParameters::VALUE => $paymentParams->getAmount(),
			//Ingrese aquí la moneda.
			PayUParameters::CURRENCY => $paymentParams->getCurrency(),
				/** The tax value. */
			PayUParameters::TAX_VALUE => $paymentParams->getTax(),
			/** The tax return base. */
			PayUParameters::TAX_RETURN_BASE => $paymentParams->getTaxBase(),
			
			//Ingrese aquí el email del comprador.
			PayUParameters::BUYER_EMAIL => $paymentParams->getBuyerEmail(),
			//Ingrese aquí el nombre del pagador.
			PayUParameters::PAYER_NAME => $paymentParams->getPayerName(),
			//Ingrese aquí el documento de contacto del pagador.
			PayUParameters::PAYER_DNI=> $paymentParams->getPayerDni(),
			
			//Ingrese aquí el nombre del método de pago
			//"SANTANDER"||"SCOTIABANK"||"IXE"||"BANCOMER"||PaymentMethods::OXXO||PaymentMethods::SEVEN_ELEVEN
			PayUParameters::PAYMENT_METHOD => $paymentParams->getPaymentMethod(),
		   
			//Ingrese aquí el nombre del pais.
			PayUParameters::COUNTRY => $paymentParams->getCountry(),
			
			//Ingrese aquí la fecha de expiración. Sólo para OXXO y SEVEN_ELEVEN
			PayUParameters::EXPIRATION_DATE => $paymentParams->getExpirationDate(),
			//IP del pagadador
			PayUParameters::IP_ADDRESS => $paymentParams->getIpAddress()
		   
		);
		$result = array();
		$code = 0001;
		$rawResult = '';

		//Action
		try {
			$response = PayUPayments::doAuthorizationAndCapture($parameters);
			$rawResult = $response;

			if ($response) {
				$result['code'] 	= $code;
				$result['message'] 	= str_replace('{OPERACION@OPERACION}', 'PayUPayments::doAuthorizationAndCapture', $this->getMessage($code));
				$result['response']['order'] 		= $response->transactionResponse->orderId;
				$result['response']['transaction'] 	= $response->transactionResponse->transactionId;
	
				if($response->transactionResponse->state=="PENDING"){
					 $status = 0005;
					 $result['response']['pendingReason'] = str_replace('{RAZON@RAZON}',$response->transactionResponse->pendingReason,$this->getMessage($status));
					 $result['response']['reference']	  = $response->transactionResponse->extraParameters->REFERENCE;
					 $result['response']['urlReceipt'] 	  = '<a class="btn" id="print" href="'.$response->transactionResponse->extraParameters->URL_PAYMENT_RECEIPT_HTML.'" target="_blank">Ver Comprobante</a>';					
				}
				if(isset($response->transactionResponse->responseCode)){
					$result['response']['responseCode'] = $response->transactionResponse->responseCode;
				}

				
				$logdata['invoiceid'] 		= $paymentParams->getReference();
				$logdata['orderid'] 		= $result['response']['order'];
				$logdata['transaction']		= $result['response']['transaction'];
				$logdata['status'] 			= $result['response']['state'];
				$logdata['paymentmethod'] 	= $paymentParams->getPaymentMethod();
				$logdata['message']			= $result['message'];
				$logdata['urlReceipt']		= $result['response']['urlReceipt'];
				$logdata['fecha']			= date("Y-m-d H:i:s");
				$logdata['expiracion']		= $paymentParams->getExpirationDate();

				LogData::saveData($logdata);
				
			}
		  
		} catch (Exception $e) {
			$code = 0000;
			$result['code'] 		= $code;
			$result['message'] 		= str_replace('{OPERACION@OPERACION}', 'PayUPayments::doAuthorizationAndCapture', $this->getMessage($code));
			$result['exception'] 	= $e->getMessage();
		}

		$notificacion = '';
		$notificacion .= "Parametros:\n";
		$notificacion .= json_encode($parameters);
		$notificacion .= "\n\n";
		$notificacion .= "Respuesta:\n";
		$notificacion .= json_encode($result);
		$notificacion .= "\n\n";
		$notificacion .= "Respuesta (RAW):\n";
		$notificacion .= json_encode($rawResult);


		//Result
		return $result;
	}

	public function getMessage($code){

		$messages = array(
				0000 => 'La operacion {OPERACION@OPERACION} tuvo un error',
				0001 => 'La operacion {OPERACION@OPERACION} se ejecuto exitosamente',
				0002 => 'No se puede conectar a la interface de PayU, por favor <a class="btn btn-conoce" href="https://supanel.suempresa.com/submitticket.php?step=2&amp;deptid=39" target="_blank">LEVANTA UN TICKET</a>.',
				0003 => 'Su pago fue Aprobado',
				0004 => 'Su pago esta Pendiente',
				0005 => 'Su pago esta pendiente debido a: {RAZON@RAZON}',
				0006 => 'Su pago fue Rechazado',
				0007 => 'El estatus de su pago es: {STATUS@STATUS}'
			);

		return $messages[$code];

	}

}


/**
* Parametros del Gateway
*/
class PaymentParams
{
	
	//Ingrese aquí el código de referencia.
	var $reference 	 = '';
	//Ingrese aquí la descripción.
	var $description = '';
	
	// -- Valores --
	//Ingrese aquí el valor.        
	var $amount 	= '0.00';
	//Ingrese aquí la moneda.
	var $currency 	= "MXN";
	/** The tax value. */
	var $tax 		= '16.00';
	/** The tax return base. */
	var $taxBase 	= '100.00';
	
	// -- Comprador 
	//Ingrese aquí el nombre del comprador.
	var $buyerName 		= '';
	//Ingrese aquí el email del comprador.
	var $buyerEmail 	= '';
	//Ingrese aquí el teléfono de contacto del comprador.
	var $buyerPhone 	= '';
	//Ingrese aquí el documento de contacto del comprador.
	var $buyerDni 		= '0000000000000';
	//Ingrese aquí la dirección del comprador.
	var $buyerAddress1 	= '';
	var $buyerAddress2 	= '';
	var $buyerCity 		= '';
	var $buyerState 	= '';
	var $buyerCountry 	= PayUCountries::MX;
	var $buyerPostcode 	= '';
	
	// -- pagador --
	//Ingrese aquí el nombre del pagador.
	var $payerName 		= '';
	//Ingrese aquí el email del pagador.
	var $payerEmail 	= '';
	//Ingrese aquí el teléfono de contacto del pagador.
	var $payerPhone 	= '';
	//Ingrese aquí el documento de contacto del pagador.
	var $payerDni 		= '0000000000000';

	//Ingrese aquí la dirección del pagador.
	var $payerAddress1 	= '';
	var $payerAddress2 	= '';
	var $payerCity 		= '';
	var $payerState 	= '';
	var $payerCountry 	= PayUCountries::MX;
	var $payerPostcode 	= '';
	var $birthDate		= '';
	
	// -- Datos de la tarjeta de crédito -- 
	//Ingrese aquí el número de la tarjeta de crédito
	var $creditCardNumber = '';
	//Ingrese aquí la fecha de vencimiento de la tarjeta de crédito
	var $creditCardExpirationDate = '';
	//Ingrese aquí el código de seguridad de la tarjeta de crédito
	var $creditCardSecurityCode = '';
	//Ingrese aquí el nombre de la tarjeta de crédito
	var $paymentMethod = '';
	
	//Ingrese aquí el número de cuotas.
	var $installmentsNumber = "1";
	//Ingrese aquí el nombre del pais.
	var $country = PayUCountries::MX;
	
	//Session id del device.
	var $deviceSessionId = '';
	//IP del pagadador
	var $ipAddress = '';
	//Cookie de la sesión actual.
	var $payerCookie = '';
	//Cookie de la sesión actual.        
	var $userAgent = '';

	var $expirationDate = '';


	public function getReference(){
		return $this->reference;
	}

	public function setReference($reference){
		$this->reference = $reference;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getAmount(){
		return $this->amount;
	}

	public function setAmount($amount){
		$this->amount = $amount;
	}

	public function getCurrency(){
		return $this->currency;
	}

	public function setCurrency($currency){
		$this->currency = $currency;
	}

	public function getTax(){
		return $this->tax;
	}

	public function setTax($tax){
		$this->tax = $tax;
	}

	public function getTaxBase(){
		return $this->taxBase;
	}

	public function setTaxBase($taxBase){
		$this->taxBase = $taxBase;
	}

	# Buyer

	public function getBuyerName(){
		return $this->buyerName;
	}

	public function setBuyerName($buyerName){
		$this->buyerName = $buyerName;
	}

	public function getBuyerEmail(){
		return $this->buyerEmail;
	}

	public function setBuyerEmail($buyerEmail){
		$this->buyerEmail = $buyerEmail;
	}

	public function getBuyerPhone(){
		return $this->buyerPhone;
	}

	public function setBuyerPhone($buyerPhone){
		$this->buyerPhone = $buyerPhone;
	}

	public function getBuyerDni(){
		return $this->buyerDni;
	}

	public function setBuyerDni($buyerDni){
		$this->buyerDni = $buyerDni;
	}

	public function getBuyerAddress1(){
		return $this->buyerAddress1;
	}

	public function setBuyerAddress1($buyerAddress1){
		$this->buyerAddress1 = $buyerAddress1;
	}

	public function getBuyerAddress2(){
		return $this->buyerAddress2;
	}

	public function setBuyerAddress2($buyerAddress2){
		$this->buyerAddress2 = $buyerAddress2;
	}

	public function getBuyerCity(){
		return $this->buyerCity;
	}

	public function setBuyerCity($buyerCity){
		$this->buyerCity = $buyerCity;
	}

	public function getBuyerState(){
		return $this->buyerState;
	}

	public function setBuyerState($buyerState){
		$this->buyerState = $buyerState;
	}

	public function getBuyerCountry(){
		return $this->buyerCountry;
	}

	public function setBuyerCountry($buyerCountry){
		$this->buyerCountry = $buyerCountry;
	}

	public function getBuyerPostcode(){
		return $this->buyerPostcode;
	}

	public function setBuyerPostcode($buyerPostcode){
		$this->buyerPostcode = $buyerPostcode;
	}

	# Payer
	
	public function getPayerName(){
		return $this->payerName;
	}

	public function setPayerName($payerName){
		$this->payerName = $payerName;
	}

	public function getPayerEmail(){
		return $this->payerEmail;
	}

	public function setPayerEmail($payerEmail){
		$this->payerEmail = $payerEmail;
	}

	public function getPayerPhone(){
		return $this->payerPhone;
	}

	public function setPayerPhone($payerPhone){
		$this->payerPhone = $payerPhone;
	}

	public function getPayerDni(){
		return $this->payerDni;
	}

	public function setPayerDni($payerDni){
		$this->payerDni = $payerDni;
	}

	public function getPayerAddress1(){
		return $this->payerAddress1;
	}

	public function setPayerAddress1($payerAddress1){
		$this->payerAddress1 = $payerAddress1;
	}

	public function getPayerAddress2(){
		return $this->payerAddress2;
	}

	public function setPayerAddress2($payerAddress2){
		$this->payerAddress2 = $payerAddress2;
	}

	public function getPayerCity(){
		return $this->payerCity;
	}

	public function setPayerCity($payerCity){
		$this->payerCity = $payerCity;
	}

	public function getPayerState(){
		return $this->payerState;
	}

	public function setPayerState($payerState){
		$this->payerState = $payerState;
	}

	public function getPayerCountry(){
		return $this->payerCountry;
	}

	public function setPayerCountry($payerCountry){
		$this->payerCountry = $payerCountry;
	}

	public function getPayerPostcode(){
		return $this->payerPostcode;
	}

	public function setPayerPostcode($payerPostcode){
		$this->payerPostcode = $payerPostcode;
	}

	public function getPayerBirthDate(){
		return $this->birthDate;
	}

	public function setPayerBirthDate($birthDate){
		$this->birthDate = $birthDate;
	}

	# Credit Card

	public function getCreditCardNumber(){
		return $this->creditCardNumber;
	}

	public function setCreditCardNumber($creditCardNumber){
		$this->creditCardNumber = $creditCardNumber;
	}

	public function getCreditCardExpirationDate(){
		return $this->creditCardExpirationDate;
	}

	public function setCreditCardExpirationDate($creditCardExpirationDate){
		$this->creditCardExpirationDate = $creditCardExpirationDate;
	}

	public function getCreditCardSecurityCode(){
		return $this->creditCardSecurityCode;
	}

	public function setCreditCardSecurityCode($creditCardSecurityCode){
		$this->creditCardSecurityCode = $creditCardSecurityCode;
	}

	public function getPaymentMethod(){
		return $this->paymentMethod;
	}

	public function setPaymentMethod($paymentMethod){
		$this->paymentMethod = $paymentMethod;
	}

	public function getInstallmentsNumber(){
		return $this->installmentsNumber;
	}

	public function setInstallmentsNumber($installmentsNumber){
		$this->installmentsNumber = $installmentsNumber;
	}

	public function getCountry(){
		return $this->country;
	}

	public function setCountry($country){
		$this->country = $country;
	}

	public function getDeviceSessionId(){
		return $this->deviceSessionId;
	}

	public function setDeviceSessionId($deviceSessionId){
		$this->deviceSessionId = $deviceSessionId;
	}

	public function getIpAddress(){
		return $this->ipAddress;
	}

	public function setIpAddress($ipAddress){
		$this->ipAddress = $ipAddress;
	}

	public function getPayerCookie(){
		return $this->payerCookie;
	}

	public function setPayerCookie($payerCookie){
		$this->payerCookie = $payerCookie;
	}

	public function getUserAgent(){
		return $this->userAgent;
	}

	public function setUserAgent($userAgent){
		$this->userAgent = $userAgent;
	}

	public function getExpirationDate(){
		return $this->expirationDate;
	}

	public function setExpirationDate($expirationDate){
		$this->expirationDate = $expirationDate;
	}
}


