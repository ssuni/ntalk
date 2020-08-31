<?php namespace App\Controllers;

use App\Models\Api\Oauth_m;
use App\Models\Api\Favorite_m;
use Redis;

class Auth extends BaseController
{
    protected $view;
    public function __construct()
    {
        $this->view = \Config\Services::renderer();
        $this->validation = \Config\Services::validation();
        $this->model = new Oauth_m();
        helper('function,url');
    }

    public function _remap($method)
    {
        echo view('inc/header_v');

        if($method == 'index' || $method == 'do_login' || $method == 'sms' || $method == 'register_additional'
            || $method == 'terms' || $method == 'register_additional' || $method == 'register' || $method == 'mypage'){
            echo view('inc/menu_v');
        }

        if($method == 'mypage') {
            echo view('inc/top_v');
            echo view('inc/aside_v');
        }

        if($method)
        {
            $this->$method();
        }
        if($method == 'mypage') {
            echo view('inc/bside_v');
        }
        echo view('inc/footer_v');
    }

    public function index()
    {
        if($this->session->id){
//            echo $this->session->get('id');
        }
        echo view('login_v');
    }

    public function register()
    {
        echo view('register_v');
    }

    public function register_additional()
    {
        echo view('register_additional_v');
    }

    public function sms()
    {
        echo view('sms_v');
    }

    public function sms_request()
    {
        $phone = $this->request->getGet('phone');
    }


    public function find_id()
    {
        echo view('find_id_v');
    }

    public function find_pass()
    {
        echo view('find_pass_v');
    }

    public function do_login()
    {
        $id = $this->request->getGetPost('user');
        $password = $this->request->getGetPost('password');

        if (!$this->validation->run($this->request->getGetPost() ,'signup'))
        {
            if($this->validation->hasError('user')){
                alert_continue($this->validation->getError('user'));
                echo $this->view->setData ([ 'user' => $id ,  'password' => $password ])->render('login_v');
                exit;
            }
//            if($this->validation->hasError('password')){
//                alert_continue($this->validation->getError('password'));
//                echo $this->view->setData ([ 'user' => $id ,  'password' => $password ])->render('login_v');
//                exit;
//            }
        }else{
            if($id == ""){
                alert('아이디를 입력하세요.');
            }
            if($password == ""){
                alert('비밀번호를 입력하세요.');
            }

            $data = $this->model->user_info($id);

            if($data) {
                if (password_verify($password, $data->password)) {

//                    if($this->session->get('social')['type'] !== null){
//                        $type = $this->session->get('social')['type'];
//                    }else{
//                        $type = 'email';
//                    }
//                    $this->redis_del($id);
                    if($data->f_tempname) {
                        $profile_real = profileFolder.$data->file_url_real.$data->f_tempname;
                        $profile_thum = profileFolder.$data->file_url_thumb.$data->f_tempname;
                    }else{
                        $profile_real = "";
                        $profile_thum = "";
                    }
                    if($data->location) {
                        $location = $data->location;
                    }else{
                        $location = "";
                    }

                    if($data->location2){
                        $location2 = $data->location2;
                    }else{
                        $location2 = "";
                    }
                    $info = array(
                        'idx'          => $data->idx,
                        'id'           => $data->id,
                        'phone'        => $data->phone,
                        'nickname'     => $data->nickname,
                        'location'     => $location,
                        'location2'    => $location2,
                        'gender'       => $data->gender,
                        'age'          => $data->age,
                        'type'         => $data->type,
                        'profile_real' => $profile_real,
                        'profile_thum' => $profile_thum,
                        'os'           => 'web',
                        'hash'         => $data->hash,
                        'user_create_at' => $data->create_at
                    );
                    $this->session->set($info);
                    $this->session->set('user_details',json_encode($info));

                    $this->model->last_login($data->id);
                    $ip = $this->request->getIPAddress();
                    $this->model->last_login_ip($data->id,$ip);
                    $this->insertUserLog();
                    $this->response->redirect('/main');

                } else {
                    alert('아이디 또는 비밀번호가 올바르지 않습니다.');
//                    return redirect()->to('/auth');
//                    echo $this->view->setData ([ 'user' => $id ,  'password' => $password ])->render('login_v');
                }
            }else{
                alert('아이디 또는 비밀번호가 일치하지 않습니다.');
//                return redirect()->to('/auth');
//                echo $this->view->setData ([ 'user' => $id ,  'password' => $password ])->render('login_v');
            }
        }
    }

    public function mypage()
    {
        echo view('mypage_v');
    }

    public function terms()
    {
        echo view('terms_v');
    }

    public function redis_del($id)
    {
        $redis = new Redis();
        $redis_host = "127.0.0.1";

        $redis_port = 6379;
        $redis->connect($redis_host, $redis_port, 1000);
        if ( !$redis->select(6) ){
            exit( "NOT DB Select");
        }

        $allKeys = $redis->keys('ci_session:*');
        $user_idx = array();
        $reuslt_key = array();
        $reuslt_del = array();
        for ($i=0; $i<count($allKeys); $i++) {
            $jsonData = $redis->get($allKeys[$i]);

            array_push($reuslt_key,$jsonData);

            preg_match_all('/\{([^{}]+)\}/', $jsonData, $matches);
            $result1 = json_decode(implode('', $matches[0]),true);
            if($result1['id'] == $id){
                array_push($user_idx,$result1['idx']);
                array_push($reuslt_del,$allKeys[$i]);
            }
        }

        foreach ($reuslt_del as $rd){
            $redis->del($rd);
        }
    }

    public function phone_limit_count()
    {
        $phone = $this->request->getGetPost('phone');
        $phone_limit_count = $this->model->phone_limit_count($phone);

        if($phone_limit_count >= 3){
            alert_continue('전화번호 가입횟수 제한 초과');
            echo $this->view->setData ([ 'phone' => $phone ])->render('sms_v');
        }
    }

}

