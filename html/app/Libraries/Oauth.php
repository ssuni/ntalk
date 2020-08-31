<?php namespace App\Libraries;

class Oauth
{
    protected $type;
    protected $oauth;
    protected $state;
    protected $config;
    protected $request;
    protected $session;

    public function __construct()
    {
        $this->state = md5(microtime().mt_rand());
        $this->config = new \Config\Oauth();
        $this->request = \Config\Services::request();
        $this->session = \Config\Services::session();
        $this->authModel = new \App\Models\Api\Oauth_m();
    }

    public function login($type)
    {
        $this->typeCheck($type);
        return $this->get();
    }

    public function verification($type)
    {
        $this->typeCheck($type);
        if( dot_array_search('state', $this->session->get()) <> $this->request->getGet('state')) {
            alert('검증코드 오류','/auth');
        }
        if($type == 'naver') {
            if($this->request->getGet('error') && $this->request->getGet('error_description'))
            {
                echo "에러 핸들링";
                echo "<hr>";
                print_r($this->request->getGet());
                exit;
            }
            $res = $this->authorization();

            $return = $res;
            $return['type'] = $type;
            return $return;
        }
    }

    public function unlink($type)
    {
        $client = \Config\Services::curlrequest();
        if($type == 'naver')
        {
            if(dot_array_search('user_details', $this->session->get()) == null) alert('로그인후 이용해 주세요.','/home');
            $url = $this->reset_token($type);
            $response = $client->request('GET', $url);
            $statuscode = $response->getStatusCode();
            if($statuscode == 200) {
                $return = $response->getBody();
                $return = json_decode($return, true);
                $this->error_check($return);
                $access_token = dot_array_search('access_token', $return);

                $user_detail = $this->session->get('user_details');
                $this->authModel->delete_users(json_decode($user_detail)->id);

                $query = array(
                    'grant_type' => 'delete',
                    'client_id' => $this->oauth['client_id'],
                    'client_secret' => $this->oauth['client_secret'],
                    'access_token' => $access_token,
                    'service_provider' => 'NAVER'
                );
                $url = $this->oauth['token'].'?'.http_build_query($query);
                $response = $client->request('GET', $url);
                session_destroy();
                alert('정상적으로 처리되었습니다.', '/auth', 1);
            }
        }
    }

    private function authorization()
    {
        $client = \Config\Services::curlrequest();
        if($this->type == 'naver') {
            $query = array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->oauth['client_id'],
                'client_secret' => $this->oauth['client_secret'],
                'redirect_uri' => $this->oauth['callback'],
                'code' => $this->request->getGet('code'),
                'state' => $this->request->getGet('state')
            );
            $url = $this->oauth['token'] .'?'. http_build_query($query);
        }
        $response = $client->request('GET', $url);
        $statuscode = $response->getStatusCode();
        if($statuscode == 200) {
            $return = $response->getBody();
            $return = json_decode($return, true);
            $this->error_check($return);
            $refresh_token = dot_array_search('refresh_token', $return);
            $access_token = dot_array_search('access_token', $return);
        } else {
            $this->status_check($statuscode);
        }
        $response = $client->request('GET', $this->oauth['me'],[
            'headers' => [
                'Authorization' => dot_array_search('token_type', $return)." ".dot_array_search('access_token', $return)
            ]
        ]);
        $statuscode = $response->getStatusCode();
        if($statuscode == 200) {
            $return = json_decode($response->getBody(),true);
            $return['refresh_token'] = $refresh_token;
            $return['access_token']  = $access_token;
            return $return;
        } else {
            $this->status_check($statuscode);
        }
    }

    private function error_check($res)
    {
        if($this->type == 'naver') {
            $error_msg['invalid_request'] = '파라미터가 잘못되었거나 요청문이 잘못되었습니다.';
            $error_msg['unauthorized_client'] = '인증받지 않은 인증 코드(authorization code)로 요청했습니다.';
            $error_msg['unsupported_response_type'] = '정의되지 않은 반환 형식으로 요청했습니다.';
            $error_msg['server_error'] = '네이버 인증 서버의 오류로 요청을 처리하지 못했습니다.';
            if(dot_array_search('error', $res)) {
                exit(dot_array_search(dot_array_search('error', $res), $error_msg));
            }
        }
    }

    private function status_check($code)
    {
        if($this->type == 'naver') {
            $status_msg['401'] = '네이버 인증에 실패했습니다.';
            $status_msg['403'] = '호출 권한이 없습니다.';
            $status_msg['404'] = '검색 결과가 없습니다.';
            $status_msg['500'] = '데이터베이스 오류입니다.';
            if(dot_array_search($code, $status_msg)) {
                exit(dot_array_search($code, $status_msg));
            }
        }
    }

    private function get()
    {
        if( dot_array_search('idx', $this->session->get()) ) {
            alert('로그인중엔 이용 불가능','/');
        }
        $this->session->set('state', $this->state);
        if($this->type == 'naver') {
            $query = array(
                'response_type' => 'code',
                'client_id' => $this->oauth['client_id'],
                'redirect_uri' => $this->oauth['callback'],
                'state' => $this->state
            );

            return $this->oauth['authorize'].'?'.http_build_query($query);
        }
    }

    private function reset_token($type)
    {
        $this->typeCheck($type);
        if ($type == "naver")
        {
            $user_detail = $this->session->get('user_details');
            $idx = json_decode($user_detail)->idx;
            $token = $this->authModel->get_token($idx);
            var_dump($token);

            $query = array(
                'grant_type' => 'refresh_token',
                'client_id' => $this->oauth['client_id'],
                'client_secret' => $this->oauth['client_secret'],
                'refresh_token' => $token->refresh_token
            );
            return $this->oauth['token'].'?'.http_build_query($query);
        }
    }

    private function typeCheck($type)
    {
        $this->type = $type;
        if( ! in_array($this->type, $this->config->case)) {
            exit("에러메세지 로그인 유형오류");
        }
        $this->oauth = $this->config->api[$this->type];
    }
}
