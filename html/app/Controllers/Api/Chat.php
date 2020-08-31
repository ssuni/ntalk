<?php namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Api\Chat_m;
use App\Models\Api\Oauth_m;
use MongoDB;
use App\Libraries\Jsonwebtotokens;
use Linkhub\Popbill\PopbillMessaging;
use CodeIgniter\I18n\Time;

class Chat extends BaseController
{
    public function __construct()
    {
        $this->model = new Chat_m();
        $this->Oauth = new Oauth_m();
    }

    public function chat_declaration()
    {
        $reportId = $this->request->getGetPost('reportId');
        $userId = $this->request->getGetPost('userId');
        $now = date('Y-m-d H:m:s');

        if($reportId == "" || $userId == ""){
            $respond = $this->res(404, '파라미터 오류');
            return $this->response->setJSON($respond);
        }

        $result = $this->model->get_declaration($reportId,$userId);

        //중복검사
        if($result){
            $respond = $this->res(409, 'duplication');
            return $this->response->setJSON($respond);
        }

        $data = array(
            'document_id' => $reportId,
            'reporter'    => $userId,
            'create_at'   => $now,
            'ymd'         => date('Ymd'),
            'hms'         => date('Hms')
         );

        $last_id = $this->model->insert_declaration($data);
        if($last_id){
            $respond = $this->res(200, '등록완료');
            return $this->response->setJSON($respond);
        }else{
            $respond = $this->res(500, '등록오류');
            return $this->response->setJSON($respond);
        }
    }

    public function chat_declaration_list()
    {
        $all_id = $this->all_id();

        $arr_id = array();
        foreach ($all_id as $value){
            $arr_id[] = new MongoDB\BSON\ObjectID($value);
        }
        $delaration_list = $this->model->delaration_list($arr_id);

        if($delaration_list) {
            echo '<pre>';
            var_dump($delaration_list);
        }
    }

    public function all_id()
    {

        $year = date('Y',time());
        $month = date('m',time());

        echo $year.$month;

        $all_id = $this->model->all_id();
        return $all_id;
    }

    public function partner_info()
    {
        $type = $this->request->getHeaderLine('type');
        $authtoken = $this->request->getHeaderLine('authtoken');
        $partner = $this->request->getGetPost('user');

        if(!$authtoken){
            $respond = $this->res(400, '인증되지 않은 요청입니다.');
            return $this->response->setJSON($respond);
        }
        $partner_info = $this->Oauth->user_info($partner);

        if(file_exists('/home/ntalk/files/profile/' . $partner_info->file_url_real . $partner_info->f_tempname)) {
            $returnData = array(
                'nickname' => $partner_info->nickname,
                'profile_real' => 'https://files.ntalk.me/profile/' . $partner_info->file_url_real . $partner_info->f_tempname,
                'profile_thumb' => 'https://files.ntalk.me/profile/' . $partner_info->file_url_thumb . $partner_info->f_tempname,
            );
        }else{
            $returnData = array(
                'nickname' => $partner_info->nickname,
                'profile_real' => null,
                'profile_thumb' => null
            );
        }

        if($partner_info){
            $respond = $this->res(200, '대화상대 정보확인', $returnData);
            return $this->response->setJSON($respond);
        }else{
            $respond = $this->res(404, '상대정보가 없습니다.');
            return $this->response->setJSON($respond);
        }
    }

}