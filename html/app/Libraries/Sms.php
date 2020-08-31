<?php namespace App\Libraries;
include APPPATH . 'ThirdParty/Popbill/PopbillMessaging.php';
use Popbill\MessagingService\MessagingService;

class Sms
{

    static function SendSMS()
    {
        $LinkID = 'SELFNICK';
        // 비밀키
        $SecretKey = 'eoET39cT8DmGWf+qELLQ9r1jAquyDL1Gy/kQyOHlt9I=';
        //통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
        //STREAM 사용시에는 allow_url_fopen = on 으로 설정해야함.
        $MessagingService = new MessagingService($LinkID, $SecretKey);
        // 연동환경 설정값, 개발용(true), 상업용(false)
        $MessagingService->IsTest(false);
        // 팝빌 회원 사업자번호, "-"제외 10자리
        $testCorpNum = '2193081846';
        // 예약전송일시(yyyyMMddHHmmss) ex) 20151212230000, null인 경우 즉시전송
        $reserveDT = null;
        // 광고문자 전송여부
        $adsYN = false;
        // 전송요청번호
        // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
        // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
        $requestNum = '';
        $Messages[] = array(
            'snd' => '15228276',		// 발신번호
            'sndnm' => '발신자명',			// 발신자명
            'rcv' => '01082308203',			// 수신번호
            'rcvnm' => '수신자성명',		// 수신자성명
            'msg'	=> '안녕하세요.'	// 개별 메시지 내용
        );

        try {
//            $receiptNum = $MessagingService->SendSMS($testCorpNum, '', '', $Messages, $reserveDT, $adsYN, '', '', '', $requestNum);
//            print_r($receiptNum);
        } catch(PopbillException $pe) {
            $code = $pe->getCode();
            $message = $pe->getMessage();
            print_r($code);
            echo "<hr>";
            print_r($message);
        }
    }

}
