<?php namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Api\Timeline_m;
use App\Models\Api\Fcm_m;
use App\Models\Api\Oauth_m;
use CodeIgniter\I18n\Time;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;

class Fcm extends BaseController
{
    public function __construct()
    {
//        $this->serverKey = 'AAAAUbhK_kg:APA91bHSrpEDFx72rUZocQhde5qPICZkv7nZgDlcCcPiFXnatAMNPrI67iQsDvxespR_FM7wfDGe3kShEsOmHgt3ep99468qMMnA7kI8bon-3usi8UdPPQCdvV1Jc5U22ZEfgquKaq4Q';
        $this->serverKey = 'AIzaSyCQir3p9T2h4SboGUU60PHfVPB-g3JMpNA';
        $this->fcm_m = new Fcm_m();
    }


    public function get_token()
    {
        $limit = 10;
        $total = count($this->fcm_m->findAll());
        $page = ceil($total/$limit);

        $arr = array();
        for($i = 1; $i<=$page; $i++) {
            if($i ==  1){
                $sqlLimit = 0;
            }else{
                $sqlLimit = ($i - 1) * $limit;
            }
            $builder = $this->fcm_m;
            $builder->select('fcm_token');
            $builder->limit(10,$sqlLimit);
            $builder->orderBy('d_idx','asc');
            $query = $builder->get();
            $result = $query->getResultArray();

            $tokens = array();
            foreach ($result as $value)
            {
                array_push($tokens,$value['fcm_token']);
            }
            array_push($arr , $tokens);
        }
        return $arr;
    }


    public function send()
    {
        $title = $this->request->getGetPost('title');
        $body = $this->request->getGetPost('body');
        $tokens = $this->get_token();

        $resArray = array();
        for($i = 0; $i < count($tokens); $i++){
            $data = array(
                'registration_ids' => $tokens[$i],
                "notification" => array("body" => $body,
                    "title" => $title,
                    "icon" => "ic_launcher"
                ),
//            'data' => json_decode(array('test'))
                'data' => array( 'response' =>  json_encode('test'))
            );
            $response = $this->send_push($data);

            array_push($resArray,$response);
        }
        var_dump($resArray);
    }

    public function send_push()
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$this->serverKey
        );

        //test
        $data = array(
            'registration_ids' => array('cTXVYGz7Er0:APA91bFdqBTCORE1x0F3TAqdVbr2zaBRoZcC54KErG0GzyPsUBnqJ6y4y1m5cgL31bJcWOoYnYta6GPmBHeQAO-PKSZpydsabMKByMsiyPjysyqJV6wbBKoU1l6q2Lb8xd6BuY3FPSOB',
                        'evcoy6AKxX4:APA91bG1OXqOmsCB-MUdJfKmO9_ViG1zLlpRCm_ZRH-F2XeUURu11gTUcyCgZXsel5YiuvkHf3uD1DEIMD2vEuy0Bb9YM7GSAquRd_vPbwyMJA1V1yvPUnMuf8wlMtB74MgI-JXVQBYv'),
//            'to' => 'fD29TYl4A3Q:APA91bEZKBYSgNX41KL2GsPpzzcARiuA17_oXnRC1MTPbbppSL2zKtTMhx2Dgpyu2Le-9qht0JxY_3i2-d8PagHpOPkaF7RS9V5dnmG9X7gtlPg-8SG-8JCZedaoW9Lfcft3O1UPLbqD',
            "notification" => array("body" => 'test',
                "title" => 'test',
                "icon" => "ic_launcher"
            ),
//            'data' => json_decode(array('test'))
            'data' => array( 'response' =>  json_encode('test'))
        );
        //

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($result);

        var_dump($result->results[0]);

        return $result;
    }

}

