<?php namespace Popbill\Linkhub;
use Exception;
/**
* =====================================================================================
* Class for develop interoperation with Linkhub APIs.
* Functionalities are authentication for Linkhub api products, and to support
* several base infomation(ex. Remain point).
*
* This module uses curl and openssl for HTTPS Request. So related modules must
* be installed and enabled.
*
* http://www.linkhub.co.kr
* Author : Kim Seongjun (pallet027@gmail.com)
* Contributor : Jeong Yohan (code@linkhub.co.kr)
* Written : 2017-08-29
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anythings.
*
* Update Log
* - 2017/08/29 GetPartnerURL API added
* ======================================================================================
*/

define('LINKHUB_COMM_MODE','CURL');

class Linkhub
{
	const VERSION = '1.0';
	const ServiceURL = 'https://auth.linkhub.co.kr';
	private $__LinkID;
	private $__SecretKey;
	private $__requestMode = LINKHUB_COMM_MODE;

	private static $singleton = null;

	public function __construct()
	{
		$this->__requestMode = 'CURL';
		$this->__LinkID = 'SELFNICK';
		$this->__SecretKey = 'eoET39cT8DmGWf+qELLQ9r1jAquyDL1Gy/kQyOHlt9I=';
		// 링크아이디
		$LinkID = 'SELFNICK';

		// 비밀키
		$SecretKey = 'eoET39cT8DmGWf+qELLQ9r1jAquyDL1Gy/kQyOHlt9I=';
	}

	public static function getInstance($LinkID,$secretKey)
	{
		if(is_null(Linkhub::$singleton)) {
			Linkhub::$singleton = new Linkhub();
		}
		Linkhub::$singleton->__LinkID = $LinkID;
		Linkhub::$singleton->__SecretKey = $secretKey;

		return Linkhub::$singleton;
	}
	public function gzdecode($data){
	    return gzinflate(substr($data, 10, -8));
	}

	private function executeCURL($url,$header = array(),$isPost = false, $postdata = null) {

		if(__requestMode != "STREAM") {
			$http = curl_init($url);

			if($isPost) {
				curl_setopt($http, CURLOPT_POST,1);
				curl_setopt($http, CURLOPT_POSTFIELDS, $postdata);
			}
			curl_setopt($http, CURLOPT_HTTPHEADER,$header);
			curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($http, CURLOPT_ENCODING, 'gzip,deflate');
			$responseJson = curl_exec($http);
			$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

		    if ($responseJson != true){
			  throw new LinkhubException(curl_error($http));
		    }
			curl_close($http);

			if($http_status != 200) {
				throw new LinkhubException($responseJson);
			}
			return json_decode($responseJson);
		}
		else {
			if($isPost) {
				$params = array('http' => array(
					 'ignore_errors' => TRUE,
	   	          	 'method' => 'POST',
					 'protocol_version' => '1.0',
    	         	 'content' => $postdata
        		    ));
	        } else {
	        	$params = array('http' => array(
 	  	     		 'ignore_errors' => TRUE,
    	         	 'method' => 'GET',
					 'protocol_version' => '1.0',
        		    ));
	        }
  			if ($header !== null) {
		  		$head = "";
		  		foreach($header as $h) {
	  				$head = $head . $h . "\r\n";
	    		}
	    		$params['http']['header'] = substr($head,0,-2);
	  		}
	  		$ctx = stream_context_create($params);
	  		$response = file_get_contents($url, false, $ctx);

			$is_gzip = 0 === mb_strpos($response , "\x1f" . "\x8b" . "\x08");

			if($is_gzip){
				$response = $this->gzdecode($response);
			}

	  		if ($http_response_header[0] != "HTTP/1.1 200 OK") {
	    		throw new LinkhubException($response);
	  		}

			return json_decode($response);
		}
	}

	public function getTime()
	{
		if(__requestMode != "STREAM") {
			$http = curl_init(Linkhub::ServiceURL.'/Time');

			curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);

			$response = curl_exec($http);

			$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

      if ($response != true){
        throw new LinkhubException(curl_error($http));
      }

			curl_close($http);

      if($http_status != 200) {
				throw new LinkhubException($response);
			}
			return $response;

		} else {
			$header = array();
			$header[] = 'Connection: close';
			$params = array('http' => array(
				 'ignore_errors' => TRUE,
				'protocol_version' => '1.0',
				 'method' => 'GET'
   		    ));
			if ($header !== null) {
		  		$head = "";
		  		foreach($header as $h) {
	  				$head = $head . $h . "\r\n";
	    		}
	    		$params['http']['header'] = substr($head,0,-2);
	  		}


	  		$ctx = stream_context_create($params);

	  		$response = (file_get_contents(LInkhub::ServiceURL.'/Time', false, $ctx));

			if ($http_response_header[0] != "HTTP/1.1 200 OK") {
	    		throw new LinkhubException($response);
	  		}
			return $response;
		}
	}

	public function getToken($ServiceID, $access_id, array $scope = array() , $forwardIP = null)
	{

		$xDate = self::getTime();

		$uri = '/' . $ServiceID . '/Token';
		$header = array();

		$TokenRequest = new TokenRequest();
		$TokenRequest->access_id = $access_id;
		$TokenRequest->scope = $scope;

		$postdata = json_encode($TokenRequest);

		$digestTarget = 'POST'.chr(10);
		$digestTarget = $digestTarget.base64_encode(md5($postdata,true)).chr(10);
		$digestTarget = $digestTarget.$xDate.chr(10);
		if(!(is_null($forwardIP) || $forwardIP == '')) {
			$digestTarget = $digestTarget.$forwardIP.chr(10);
		}
		$digestTarget = $digestTarget.Linkhub::VERSION.chr(10);
		$digestTarget = $digestTarget.$uri;

		$digest = base64_encode(hash_hmac('sha1',$digestTarget,base64_decode(strtr('eoET39cT8DmGWf+qELLQ9r1jAquyDL1Gy/kQyOHlt9I=', '-_', '+/')),true));

		$header[] = 'x-lh-date: '.$xDate;
		$header[] = 'x-lh-version: '.Linkhub::VERSION;
		if(!(is_null($forwardIP) || $forwardIP == '')) {
			$header[] = 'x-lh-forwarded: '.$forwardIP;
		}

		$header[] = 'Authorization: LINKHUB SELFNICK '.$digest;
		$header[] = 'Accept-Encoding: gzip,deflate';
		$header[] = 'Content-Type: Application/json';
		$header[] = 'Connection: close';

		return self::executeCURL(Linkhub::ServiceURL.$uri , $header,true,$postdata);

	}


	public function getBalance($bearerToken, $ServiceID)
	{
		$header = array();
		$header[] = 'Authorization: Bearer '.$bearerToken;
		$header[] = 'Accept-Encoding: gzip,deflate';
		$header[] = 'Connection: close';

		$uri = '/'.$ServiceID.'/Point';

		$response = $this->executeCURL(Linkhub::ServiceURL . $uri,$header);
		return $response->remainPoint;

	}

	public function getPartnerBalance($bearerToken, $ServiceID)
	{
		$header = array();
		$header[] = 'Authorization: Bearer '.$bearerToken;
		$header[] = 'Accept-Encoding: gzip,deflate';
		$header[] = 'Connection: close';

		$uri = '/'.$ServiceID.'/PartnerPoint';

		$response = $this->executeCURL(Linkhub::ServiceURL . $uri,$header);
		return $response->remainPoint;
	}

  /*
  * 파트너 포인트 충전 팝업 URL 추가 (2017/08/29)
  */
  public function getPartnerURL($bearerToken, $ServiceID, $TOGO)
	{
		$header = array();
		$header[] = 'Authorization: Bearer '.$bearerToken;
		$header[] = 'Accept-Encoding: gzip,deflate';
		$header[] = 'Connection: close';

		$uri = '/'.$ServiceID.'/URL?TG='.$TOGO;

		$response = $this->executeCURL(Linkhub::ServiceURL . $uri, $header);
		return $response->url;
	}
}

class TokenRequest
{
	public $access_id;
	public $scope;
}

class LinkhubException extends Exception
{
	public function __construct($response, Exception $previous = null) {
       $Err = json_decode($response);
       if(is_null($Err)) {
       		parent::__construct($response, -99999999);
       }
       else {
       		parent::__construct($Err->message, $Err->code);
       }
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

?>
