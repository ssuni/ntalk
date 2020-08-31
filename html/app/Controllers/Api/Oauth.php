<?php

namespace App\Controllers\Api;

use org\bovigo\vfs\vfsStreamWrapperQuotaTestCase;
use Redis;
use App\Controllers\BaseController;
use App\Models\Api\Oauth_m;
use App\Models\Api\Mongo_m;
use App\Models\Api\Timeline_m;
use App\Models\Api\Favorite_m;
use App\Libraries\Jsonwebtotokens;
use Linkhub\Popbill\PopbillMessaging;
use CodeIgniter\I18n\Time;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;
use App\Models\Api\Files_m;

class Oauth extends BaseController
{
//    use ResponseTrait;

    public function __construct()
    {
        $this->model = new Oauth_m();
        $this->mongo_m = new Mongo_m();
        $this->files = new Files_m();
        $this->timeline_m = new Timeline_m();
        $this->favorite_m = new Favorite_m();
        helper('function');
    }

    public function social_email()
    {
        $id = $this->request->getGetPost('id');
        $type = $this->request->getGetPost('type');

        $user_info = $this->model->user_info($id);
        if ($user_info) {
            if ($user_info->type !== $type) {
                $respond = $this->res(409, '동일한 메일이 이미 존재합니다.');
                return $this->response->setJSON($respond);
            }
        } else {
            $respond = $this->res(200, '가입가능');
            return $this->response->setJSON($respond);
        }
    }

    public function redis_get()
    {
        $redis = new Redis();
        $redis_host = "127.0.0.1";
        $redis_port = 6379;
        $redis->connect($redis_host, $redis_port, 1000);
        if (!$redis->select(6)) {
            exit("NOT DB Select");
        }
        $allKeys = $redis->keys('ci_session:*');

        for ($i = 0; $i < count($allKeys); $i++) {
            $jsonData = $redis->get($allKeys[$i]);

            preg_match_all('/\\{([^{}]+)\\}/', $jsonData, $matches);
            $result = json_decode(implode('', $matches[0]), true);

            if ($result['idx']) {
                var_dump($allKeys[$i]);
                var_dump($result);
            }
        }
    }

    public function session_del()
    {
        $this->session->destroy();
        return redirect()->to('/main');
    }

    /**
     * 유저정보
     * @return 유저정보
     */
    public function getUserInfo()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $nickname = $this->request->getGetPost('nickname');

        $data = $this->model->getUserInfo($nickname);
        $data->count = $this->model->getUserInfoCount($data->uid);

        if ($data) {

            if (isset($data->f_tempname)) {
                $data->real = profileFolder . $data->file_url_real . $data->f_tempname;
                $data->thumb = profileFolder . $data->file_url_thumb . $data->f_tempname;
            }

            $another_id = $this->model->getAnotherId($data->phone,$data->uid);
            $data->anotherid = $another_id;

            $imgArr = $this->files->getFile($data->uid);

            $arr = array();
            if (count($imgArr) > 0) {
                for ($i = 0; $i < count($imgArr); $i++) {
                    if ($imgArr[$i]->division == 2) {
                        $img = array(
                            'f_idx' => $imgArr[$i]->f_idx,
                            'division' => $imgArr[$i]->division,
                            'real' => timelineFolder . $imgArr[$i]->file_url_real . $imgArr[$i]->f_tempname,
                            'thumb' => timelineFolder . $imgArr[$i]->file_url_thumb . $imgArr[$i]->f_tempname,
                            'create_at' => $imgArr[$i]->create_at
                        );
                        array_push($arr, $img);
                    }
                }

            }

            $imgArr = $this->files->getFileDesc($data->uid);

            if (count($imgArr) > 0) {
                for ($i = 0; $i < count($imgArr); $i++) {
                    if ($imgArr[$i]->division == 0) {
                        $img = array(
                            'f_idx' => $imgArr[$i]->f_idx,
                            'division' => $imgArr[$i]->division,
                            'real' => galleryFolder . $imgArr[$i]->file_url_real . $imgArr[$i]->f_tempname,
                            'thumb' => galleryFolder . $imgArr[$i]->file_url_thumb . $imgArr[$i]->f_tempname,
                            'create_at' => $imgArr[$i]->create_at
                        );
                        array_push($arr, $img);
                    }
                }

            }
            $data->files = $arr;

            if ($token || $this->session->get('id')) {
                $result = $this->likeSubmit($token, $data->uid);
//                if($this->session->get('nickname') !== '겜성') {
//
//                }
            }

            $respond = $this->res(200, 'success', $data);
            return $this->response->setJSON($respond);
        } else {
            $respond = $this->res(404, '조회된 내용이 없습니다.');
            return $this->response->setJSON($respond);
        }
    }

    /**
     * 로그인
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\Response
     */
    public function do_login()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $type = $this->request->getHeaderLine('type');
        $id = $this->request->getGetPost('id');
        $password = $this->request->getGetPost('password');
        $phone = $this->request->getGetPost('phone');
        $division = $this->request->getGetPost('division');

        $accountType = $this->request->getGetPost('type');
        //social
        $email = $this->request->getGetPost('email');

        if ($token) {

            if ($this->model->token_chk($id, $token) == 1) {
                $jsonwebtokens = new Jsonwebtotokens();
                $GetSelect = array('login_id' => $id);
                $token = $jsonwebtokens->makeJWTEncode($GetSelect);


                $this->model->token_reset($token['jwt'], $id);

                $result = $this->model->user_info($id);

                $fcm_token = $this->request->getGetPost('fcm_token');
                $os = $this->request->getGetPost('os');
                if ($fcm_token) {
                    $this->model->device_insert($fcm_token, $os, $result->idx);
                }
//                $this->redis_del($id); // Redis 삭제

                if ($result->location) {
                    $location = $result->location;
                } else {
                    $location = "";
                }

                if ($result->location2) {
                    $location2 = $result->location2;
                } else {
                    $location2 = "";
                }

                $info = array(
                    'idx' => $result->idx,
                    'id' => $result->id,
                    'phone' => $result->phone,
                    'token' => $result->token,
                    'gender' => $result->gender,
                    'age' => $result->age,
                    'nickname' => $result->nickname,
                    'location' => $location,
                    'location2' => $location2,
                    'profile_real' => profileFolder . $result->file_url_real . $result->f_tempname,
                    'profile_thum' => profileFolder . $result->file_url_thumb . $result->f_tempname,
                    'hash' => $result->hash,
                    'user_create_at' => $result->create_at
                );

                if ($result->f_tempname) {
                    $info['profile_real'] = profileFolder . $result->file_url_real . $result->f_tempname;
                    $info['profile_thum'] = profileFolder . $result->file_url_thumb . $result->f_tempname;
                } else {
                    $info['profile_real'] = "";
                    $info['profile_thum'] = "";
                }

                $cutout = $this->cutout_list($result->nickname);
                $info['cutout'] = $cutout;

                //타임라인 정보 세션 추가
                $timeline_session_data = $this->timeline_session_data($result->id);
                $info['timeline'] = $timeline_session_data;

                //즐겨찾기 정보 세션 추가
                $info['favoriteUserList'] = $this->favoriteUserList($result->id);

                $this->session->set($info);
                $this->session->set('user_details', json_encode($info));

                $this->response->setHeader('authtoken', $token['jwt']);
                $ip = $this->request->getIPAddress();
                $this->model->last_login($result->id);
                $this->model->last_login_ip($result->id, $ip);

                $this->insertUserLog($token);

                $respond = $this->res(200, '자동로그인성공', $info);
                return $this->response->setJSON($respond);
            } else {
                $respond = $this->res(400, '인증실패');
                return $this->response->setJSON($respond);
            }
        } else {  //자동 로그인

            if ($accountType !== 'email' && $accountType !== 'ntalk') {
                $user_info = $this->model->social_email($id);
                if ($user_info) {
                    if ($accountType !== $user_info->type) {
                        $respond = $this->res(409, '동일한 메일이 존재합니다.');
                        return $this->response->setJSON($respond);
                    }
                }
            }

            if ($id == '') {
                $respond = $this->res(400, 'ID를 입력하세요.');
                return $this->response->setJSON($respond);
            }
            if ($password == '') {
                $respond = $this->res(400, '패스워드를 입력하세요.');
                return $this->response->setJSON($respond);
            }
            $data = $this->model->get($id);

            if (!$data) {
                $respond = $this->res(404, '일치하는 회원정보가 없습니다.');
                return $this->response->setJSON($respond);
            }

            if ($this->session->get('social')['type']) {
                $type = $this->session->get('social')['type'];
            }else{
                $type = $this->request->getGetPost('type');
            }

            if (password_verify($password, $data->password)) {
                $jsonwebtokens = new Jsonwebtotokens();
                $GetSelect = array('login_id' => $id);
                $token = $jsonwebtokens->makeJWTEncode($GetSelect);

                $this->model->token_reset($token['jwt'], $id);

                $result = $this->model->user_info($id);

                $fcm_token = $this->request->getGetPost('fcm_token');
                $os = $this->request->getGetPost('os');
                if ($fcm_token) {
                    $this->model->device_insert($fcm_token, $os, $result->idx);
                }

                $this->redis_del($id); // Redis 삭제

                $profile_chk = $this->model->profile_chk($result->id);

                if ($result->location) {
                    $location = $result->location;
                } else {
                    $location = "";
                }

                if ($result->location2) {
                    $location2 = $result->location2;
                } else {
                    $location2 = "";
                }

                $info = array(
                    'idx' => $result->idx,
                    'id' => $result->id,
                    'phone' => $result->phone,
                    'token' => $result->token,
                    'gender' => $result->gender,
                    'age' => $result->age,
                    'nickname' => $result->nickname,
                    'location' => $location,
                    'location2' => $location2,
                    'hash' => $result->hash,
                    'type' => $type,
                    'user_create_at' => $result->create_at
                );

                if ($profile_chk) {
                    $info['profile_real'] = profileFolder . $result->file_url_real . $result->f_tempname;
                    $info['profile_thum'] = profileFolder . $result->file_url_thumb . $result->f_tempname;

                } else {
                    $info['profile_real'] = "";
                    $info['profile_thum'] = "";
                }

                $timeline_session_data = $this->timeline_session_data($result->id);

                $info['timeline'] = $timeline_session_data;
                //즐겨찾기 정보 세션 추가
                $info['favoriteUserList'] = $this->favoriteUserList($result->id);

                $this->session->set($info);
                $this->session->set('user_details', json_encode($info));

                $this->response->setHeader('authtoken', $token['jwt']);
                $ip = $this->request->getIPAddress();
                $this->model->last_login($result->id);
                $this->model->last_login_ip($result->id, $ip);
                $this->insertUserLog();

                $respond = $this->res(200, '로그인성공', $info);

                if ($division == 'pc') {
                    return redirect()->to('/main');
                } else {
                    return $this->response->setJSON($respond);
                }

            } else {
                $respond = $this->res(400, '아이디 또는 비밀번호가 일치하지 않습니다.');
                return $this->response->setJSON($respond);
            }
        }
    }

    /**
     * @param $id
     * @return array 타임라인 이미지 파일
     */
    public function timeline_session_data($id)
    {
        $timeline_info = (array)$this->timeline_m->timeline_set_session($id);
        if (!$timeline_info) {
            $this->session->set('time_line', json_encode(null));
        } else {
            $files = $this->timeline_m->allDeleteImg($id);
            $imgArr = array();
            foreach ($files as $fl) {
                $upImg = array(
                    'f_idx' => $fl->f_idx,
                    'real' => timelineFolder . $fl->file_url_real . $fl->f_tempname,
                    'thumb' => timelineFolder . $fl->file_url_thumb . $fl->f_tempname,
                );
                array_push($imgArr, $upImg);
            }
            $timeline_info['files'] = $imgArr;
            return $timeline_info;
//            $this->session->set('time_line',json_encode($timeline_info));
        }
    }

    /**
     * PC 로그인시 필요 데이터
     * @return \CodeIgniter\HTTP\Response
     */
    public function logindata()
    {
        $id = $this->request->getGetPost('id');
        $user_info = $this->model->user_info($this->session->get('id'));

        if ($user_info) {
            $info = array(
                'id' => $user_info->id,
                'gender' => $user_info->gender,
                'age' => $user_info->age,
                'nickname' => $user_info->nickname,
                'location' => $user_info->location,
                'location2' => $user_info->location2,
                'hash' => $user_info->hash,
                'real' => profileFolder . $user_info->file_url_real . $user_info->f_tempname,
                'thumb' => profileFolder . $user_info->file_url_thumb . $user_info->f_tempname
            );

            if ($user_info->file_url_real) {
                $info['real'] = profileFolder . $user_info->file_url_real . $user_info->f_tempname;
                $info['thumb'] = profileFolder . $user_info->file_url_thumb . $user_info->f_tempname;
            } else {
                $info['real'] = null;
                $info['thumb'] = null;
            }
            //즐겨찾기 정보 세션 추가
            $info['favoriteUserList'] = $this->favoriteUserList($user_info->id);
            $respond = $this->res(200, $info);
        } else {
            $respond = $this->res(404, '일치하는 회원이 없습니다.');
        }

        return $this->response->setJSON($respond);
    }

    /**
     * @param $id
     * Redis 세션중복 제거
     */
    public function redis_del($id)
    {
        $redis = new Redis();
        $redis_host = '127.0.0.1';

        $redis_port = 6379;
        $redis->connect($redis_host, $redis_port, 1000);
        if (!$redis->select(6)) {
            exit('NOT DB Select');
        }

        $allKeys = $redis->keys('ci_session:*');
        $user_idx = array();
        $reuslt_key = array();
        $reuslt_del = array();
        for ($i = 0; $i < count($allKeys); ++$i) {
            $jsonData = $redis->get($allKeys[$i]);

            array_push($reuslt_key, $jsonData);

            preg_match_all('/\{([^{}]+)\}/', $jsonData, $matches);
            $result1 = json_decode(implode('', $matches[0]), true);
            if ($result1['id'] == $id) {

                array_push($user_idx, $result1['idx']);
                array_push($reuslt_del, $allKeys[$i]);
            }
        }

        foreach ($reuslt_del as $rd) {
            $redis->del($rd);
        }
        $count = $redis->eval('return table.getn(redis.call("keys", "ci_session:*"))');
    }

    public function logout()
    {
//        $this->redis_del($this->session->get('id'));
        $this->session->destroy();

        return redirect()->to('/main');
    }

    /**
     * 회원가입 API
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\Response
     */
    public function create()
    {
        //기기 구분값
        $division = $this->request->getHeaderLine('type');

        //필수입력
        $id = $this->request->getGetPost('id');
        $password = $this->request->getGetPost('password');
        $gender = $this->request->getGetPost('gender');
        $phone = $this->request->getGetPost('phone');
        $certification = $this->request->getGetPost('certification');
        $type = $this->request->getGetPost('type');

        // 12.20일체크
//        if ($division == 'app') {
//            $certification_chk = count($this->model->sms_confirm($phone, $certification));
//
//            if ($certification_chk !== 1) {
//                $respond = $this->res(400, '인증번호가 일치하지 않습니다.');
//
//                return $this->response->setJSON($respond);
//            }
//        }


        if ($this->session->get('social')) {
            $type = $this->session->get('social')['type'];
            $password = $this->session->get('social')['pk'];
        }

        $phone_limit_count = $this->model->phone_limit_count($phone);

        if ($phone_limit_count >= 3) {
            if ($division == 'app') {
                $respond = $this->res(400, '휴대폰 번호 가입 횟수 초과 (3회)');

                return $this->response->setJSON($respond);
            } else {
                alert('전화번호 가입횟수 제한 초과');
                return redirect()->to('/auth');
            }
        }

        if ($id == '' || $password == '' || $phone == '') {
            $respond = $this->res(400, '파라미터 오류');
            return $this->response->setJSON($respond);
        }


        $social = array("naver", "kakao", "google", "facebook");
        if (!in_array($type, $social)) {
            if (!password_regex($password)) {
                if ($division == 'app') {
                    $respond = $this->res(400, '비밀번호 형식 오류');
                    return $this->response->setJSON($respond);
                }
            }
            if (!id_regex($id)) {
                $respond = $this->res(400, '아이디 형식 오류');
                return $this->response->setJSON($respond);
            }
        }

        if (!phone_regex($phone)) {
            $respond = $this->res(400, '전화번호 형식 오류');
            return $this->response->setJSON($respond);
        }

        $id_count = $this->model->get_count($id);
        $user_info = $this->model->user_info($id);

        if ($id_count >= 1) {
            $userType = $user_info->type;
            $respond = $this->res(409, '동일한 메일이 이미 존재합니다.');
            return $this->response->setJSON($respond);
        }

//        if ($division == 'app') {
//            $hash_chk = $this->model->hash_chk($phone);
//
//            if ($certification !== $hash_chk->certification) {
//                $respond = $this->res(500, '휴대폰 인증에 실패하였습니다.');
//
//                return $this->response->setJSON($respond);
//            }
//        }

        $jsonwebtokens = new Jsonwebtotokens();
        $GetSelect = array('login_id' => $id);
        $token = $jsonwebtokens->makeJWTEncode($GetSelect);
        $ip = $this->request->getIPAddress();

        $data = array(
            'id' => $id,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'gender' => $gender,
            'phone' => $phone,
            'create_at' => date('Y-m-d H:i:s'),
            'token' => $token['jwt'],
            'type' => $type,
            'hash' => md5(time()),
            'create_ip' => $ip
        );

        if ($division == "app") {
            $data['access_token'] = $this->request->getGetPost('accessToken');
            $data['refresh_token'] = $this->request->getGetPost('refreshToken');
        }

        if (isset($this->session->get('social')['type'])) {
            $data['type'] = $this->session->get('social')['type'];
            $data['access_token'] = $this->session->get('social')['access_token'];
            $data['refresh_token'] = $this->session->get('social')['refresh_token'];
        }


        //회원정보 저장
        $last_id = $this->model->insert_user($data);

        if ($last_id) {
            $this->model->phone_limit(array('id' => $id, 'phone' => $phone));

            if ($division !== 'app') { // PC일 경우
                //추가 입력사항
                $age = $this->request->getGetPost('age');
                $location = $this->request->getGetPost('location1');
                $location2 = $this->request->getGetPost('location2');

                if ($this->request->getGetPost('nickname')) {
                    $nickname = $this->request->getGetPost('nickname');
                } else {
                    //회원 닉네임 생성
                    $nickname = '회원' . time();
                }
                $user_info = $this->model->idx_user_info($last_id);
                $nick_chk = $this->model->overlap($user_info->id, 'nickname');

                if ($nick_chk > 0) {
                    $respond = $this->res(406, '닉네임중복');

                    return $this->response->setJSON($respond);
                }

                if ($age !== '' || $location !== '' || $nickname !== '') {
                    if ((1 <= $age) && ($age <= 99)) {
                        $this->model->insert_userinfo(array('uidx' => $user_info->idx, 'uid' => $user_info->id, 'age' => $age, 'location' => $location, 'location2' => $location2, 'nickname' => $nickname));
                    } else {
                        $this->model->insert_userinfo(array('uidx' => $user_info->idx, 'uid' => $user_info->id, 'age' => -1, 'location' => $location, 'location2' => $location2, 'nickname' => $nickname));
                    }
                }

                $result = $this->model->user_info($user_info->id);
            } else {
                $user_info = $this->model->idx_user_info($last_id);
                $nickname = '회원' . time();
                $random_nick_insert = $this->model->random_nick_insert($id, $user_info->idx, $nickname);
            }

            $user_info = $this->model->user_info($id);

            $profile_chk = $this->model->profile_chk($id);

            if ($user_info->location) {
                $location = $user_info->location;
            } else {
                $location = "";
            }

            if ($user_info->location2) {
                $location2 = $user_info->location2;
            } else {
                $location2 = "";
            }

            $data = array(
                'idx' => $last_id,
                'id' => $id,
                'gender' => $gender,
                'phone' => $phone,
                'nickname' => $user_info->nickname,
                'age' => $user_info->age,
                'location' => $location,
                'location2' => $location2,
                'token' => $token['jwt'],
                'hash' => $user_info->hash
            ); //Return

            if ($profile_chk) {
                $data['profile_real'] = profileFolder . $user_info->file_url_real . $user_info->f_tempname;
                $data['profile_thum'] = profileFolder . $user_info->file_url_thumb . $user_info->f_tempname;

            } else {
                $data['profile_real'] = "";
                $data['profile_thum'] = "";
            }

            $this->response->setHeader('authtoken', $token['jwt']);

            if ($division == 'app') {
                $respond = $this->res(200, '등록성공', $data);
            } else {
                $result = $this->model->user_info($id);

                $info = array(
                    'idx' => $result->idx,
                    'id' => $result->id,
                    'gender' => $result->gender,
                    'age' => $result->age,
                    'nickname' => $result->nickname,
                    'location' => $location,
                    'location2' => $location2,
                    'tag' => $result->tag,
                    'hash' => $result->hash,
                    'user_create_at' => $result->create_at
                );
                if (isset($this->session->get('social')['type'])) {
                    $info['type'] = $this->session->get('social')['type'];
                    $info['access_token'] = $this->session->get('social')['access_token'];
                    $info['refresh_token'] = $this->session->get('social')['refresh_token'];
                }
                if ($result->f_tempname) {
                    $info['profile_real'] = profileFolder . $result->file_url_real . $result->f_tempname;
                    $info['profile_thum'] = profileFolder . $result->file_url_thumb . $result->f_tempname;
                } else {
                    $info['profile_real'] = "";
                    $info['profile_thum'] = "";
                }

                $this->session->set($info);
                $this->session->set('user_details', json_encode($info));

                $this->model->last_login($user_info->id);
                $this->model->last_login_ip($user_info->id, $ip);
                $this->insertUserLog();

                return redirect()->to('/auth/register_additional');
            }
        } else {
            $respond = $this->res(500, '등록실패.');
        }

        return $this->response->setJSON($respond);
    }

    /**
     * 회원추가정보 API
     * @return \CodeIgniter\HTTP\Response
     */
    public function createAdditional()
    {
        //기기 구분값
        $division = $this->request->getHeaderLine('type');
        $token = $this->request->getHeaderLine('authtoken');

        $id = $this->request->getGetPost('id');
        $ip = $this->request->getIPAddress();

        if ($division == 'app') {
            $user_info = $this->model->token_user_info($token);
        } else {
            $user_info = $this->model->user_info($id);
        }
        //추가 입력사항
        $age = $this->request->getGetPost('age');
        $location = $this->request->getGetPost('location1');
        $location2 = $this->request->getGetPost('location2');

        if ($this->request->getGetPost('nickname')) {
            $nickname = $this->request->getGetPost('nickname');
        } else {
            //회원 닉네임 생성
            $nickname = '회원' . time();
        }

        $nick_chk = $this->model->overlap($user_info->id, 'nickname');

        if ($nick_chk > 0) {
            $respond = $this->res(406, '닉네임중복');
            return $this->response->setJSON($respond);
        }

        if ($age !== '' || $location !== '' || $nickname !== '') {
            if ((1 <= $age) && ($age <= 99)) {
                $this->model->insert_userinfo(array('uidx' => $user_info->idx, 'uid' => $user_info->id, 'age' => $age, 'location' => $location, 'location2' => $location2, 'nickname' => $nickname));
            } else {
                $this->model->insert_userinfo(array('uidx' => $user_info->idx, 'uid' => $user_info->id, 'age' => -1, 'location' => $location, 'location2' => $location2, 'nickname' => $nickname));
            }
        }

        $result = $this->model->user_info($user_info->id);
        $info = array(
            'idx' => $result->idx,
            'id' => $result->id,
            'gender' => $result->gender,
            'age' => $result->age,
            'nickname' => $result->nickname,
            'location' => $result->location,
            'location2' => $result->location2,
            'hash' => $result->hash,
        );
        if ($division !== 'app') {
            if ($imagefile = $this->request->getFile('profile')) {
                $denyfile = array('php', 'php3', 'exe', 'cgi', 'phtml', 'html', 'htm', 'pl', 'asp', 'jsp', 'inc', 'dll', 'js', 'zip');
                if (in_array($imagefile->getExtension(), $denyfile)) {
                    $respond = $this->res(400, '업로드불가 파일 포함.');
                    return $this->response->setJSON($respond);
                }
                if ($imagefile->getExtension() == 'gif') {
                    $frame = $this->AnigifCheck($imagefile->getPathName());
                    if ($frame == true) {
                        $respond = $this->res(400, '업로드불가 파일 포함.');

                        return $this->response->setJSON($respond);
                    }
                }
                if ($imagefile->getSize() >= 5242880) {
                    $respond = $this->res(400, '업로드용량 초과파일 포함.');

                    return $this->response->setJSON($respond);
                }
                $year = date('Y', time());
                $month = date('m', time());

                if ($imagefile->isValid() && !$imagefile->hasMoved()) {
                    $newName = $imagefile->getRandomName();
                    if (!is_dir(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/')) {
                        mkdir(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/', 0707, true);
                        mkdir(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/thumb/', 0707, true);
                    }
                    $imagefile->move(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/', $newName);
                    try {
                        $image = \Config\Services::image()
                            ->withFile(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/' . $newName)
                            ->fit(320, 320, 'center')//crop
//                            ->resize(320, 320, true, 'height')
                            ->save(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/thumb/' . $newName, 70);
                    } catch (CodeIgniter\Images\ImageException $e) {
                        echo $e->getMessage();
                    }
                    $f_tempname = $newName;
                    $f_name = $imagefile->getClientName();
                    $ext = $imagefile->getExtension();
                    $data = array(
                        'u_idx' => $user_info->idx,
                        'u_id' => $user_info->id,
                        'f_name' => $f_name,
                        'f_tempname' => $f_tempname,
                        'file_url_real' => $year . '/' . $month . '/',
                        'file_url_thumb' => $year . '/' . $month . '/thumb/',
                        'ext' => $ext,
                        'create_at' => date('Y-m-d H:i:s'),
                        'division' => 1,
                    );
                    $last_id = $this->model->img_upload($data);
                    if ($last_id) {
                        $profile = $this->files->getProfile($user_info->id, $last_id);
                        $info['profile_thum'] = profileFolder . $profile->file_url_thumb . $profile->f_tempname;
                        $info['profile_real'] = profileFolder . $profile->file_url_real . $profile->f_tempname;
                    }
                }
            }
        }

        $this->session->set($info);
        $this->session->set('user_details', json_encode($info));

        $this->model->last_login($user_info->id);
        $this->model->last_login_ip($user_info->id, $ip);
        $this->insertUserLog();

        if ($token) {
            $respond = $this->res(200, '추가입력완료', $info);
            return $this->response->setJSON($respond);
        } else {
            return redirect()->to('/');
        }
    }

    public function itemEditSubmit()
    {

        $id = $this->session->get('id');

        $user_info = $this->model->user_info($id);

        $nickname = $this->request->getPost('nickname');
        $age = $this->request->getPost('age');
        $gender = $this->request->getPost('gender');
        $location = $this->request->getPost('location');
        $location2 = $this->request->getPost('location2');

        $data = array(
            'id' => $id,
            'nickname' => $nickname,
            'age' => $age,
            'gender' => $gender,
            'location' => $location,
            'location2' => $location2
         );

        $before_data = $this->model->getUserInfo($user_info->nickname);

        $before = array(
            'nickname' => $before_data->nickname,
            'location' => $before_data->location,
            'location2' => $before_data->location2,
            'gender' => $before_data->gender,
            'age' => $before_data->age,
            'real' => profileFolder . $before_data->file_url_real . $before_data->f_tempname,
            'thumb' => profileFolder . $before_data->file_url_thumb . $before_data->f_tempname
        );

        $this->model->itemEditSubmit($data);

        $user_info = $this->model->user_info($id);

        $after_data = $this->model->getUserInfo($user_info->nickname);

        $after = array(
            'nickname' => $after_data->nickname,
            'location' => $after_data->location,
            'location2' => $after_data->location2,
            'gender' => $after_data->gender,
            'age' => $after_data->age,
            'real' => profileFolder . $after_data->file_url_real . $after_data->f_tempname,
            'thumb' => profileFolder . $after_data->file_url_thumb . $after_data->f_tempname
        );
        $info = array(
            'idx' => $after_data->uidx,
            'id' => $after_data->uid,
            'phone' => $after_data->phone,
            'gender' => $after_data->gender,
            'age' => $after_data->age,
            'nickname' => $after_data->nickname,
            'location' => $location,
            'location2' => $location2,
            'profile_real' => profileFolder . $after_data->file_url_real . $after_data->f_tempname,
            'profile_thum' => profileFolder . $after_data->file_url_thumb . $after_data->f_tempname,
        );

        $socket_data['before'] = $before;
        $socket_data['after'] = $after;

        $cmd = 15000;
        $socker_curl = $this->auth_socket_io($cmd, $socket_data);

        $this->session->set('user_details',json_encode($info));

        $respond = $this->res(200, '확인', $after);
        return $this->response->setJSON($respond);

    }

    /**
     * 회원 개별 수정 API
     * @return \CodeIgniter\HTTP\Response
     */
    public function itemEdit()
    {
        $division = $this->request->getHeaderLine('type');
        $token = $this->request->getHeaderLine('authtoken');
        $idx = $this->request->getGetPost();

        if ($division == "app") {
            $user_info = $this->model->token_user_info($token);
        } else {
            $idx = $this->session->get('idx');
            $user_info = $this->model->idx_user_info($idx);
        }

        $arg = $this->request->getGetPost('arg[]');

        $before_data = $this->model->getUserInfo($user_info->nickname);

        $before = array(
            'nickname' => $before_data->nickname,
            'location' => $before_data->location,
            'location2' => $before_data->location2,
            'gender' => $before_data->gender,
            'age' => $before_data->age,
            'real' => profileFolder . $before_data->file_url_real . $before_data->f_tempname,
            'thumb' => profileFolder . $before_data->file_url_thumb . $before_data->f_tempname
        );

//        $user_info = $this->user_authentication($token,$division);

        $result = $this->model->itemEdit($user_info->id, $arg);

        if ($division == "app") {
            $user_info = $this->model->token_user_info($token);
        } else {
            $user_info = $this->model->idx_user_info($idx);
        }

        $after_data = $this->model->getUserInfo($user_info->nickname);

        $after = array(
            'nickname' => $after_data->nickname,
            'location' => $after_data->location,
            'location2' => $after_data->location2,
            'gender' => $after_data->gender,
            'age' => $after_data->age,
            'real' => profileFolder . $after_data->file_url_real . $after_data->f_tempname,
            'thumb' => profileFolder . $after_data->file_url_thumb . $after_data->f_tempname
        );

        $socket_data['before'] = $before;
        $socket_data['after'] = $after;

        $cmd = 15000;
        $socker_curl = $this->auth_socket_io($cmd, $socket_data);

        $respond = $this->res(200, '확인', $result);
        return $this->response->setJSON($respond);
    }

    public function phone_edit()
    {
        $id = $this->session->get('id');
        $phone = $this->request->getPost('phone');


        $phone_limit_count = $this->model->phone_limit_count($phone);

            if ($phone_limit_count >= 3) {
                $respond = $this->res(406, '휴대폰 번호 가입 횟수 초과 (3회)');
                return $this->response->setJSON($respond);
//                }
            }
        // 회원가입 인증 횟수제한


        $result = $this->model->phone_edit($id,$phone);

        if($result == true){
            $respond = $this->res(200, '확인', $result);
            return $this->response->setJSON($respond);
        }else{
            $respond = $this->res(500, '서버오류');
            return $this->response->setJSON($respond);
        }

    }

    public function edit()
    {
        $division = $this->request->getHeaderLine('type');
        $token = $this->request->getHeaderLine('authtoken');
        $idx = $this->request->getGetPost();

        if ($division == "app") {
            $user_info = $this->model->token_user_info($token);
        } else {
            $user_info = $this->model->idx_user_info($idx);
        }

        $arr = array(
            'idx' => $user_info->idx,
            'id' => $user_info->id,
            'gender' => $user_info->gender,
            'phone' => $user_info->phone,
            'age' => $user_info->age,
            'location1' => $user_info->location,
            'location2' => $user_info->location2,
            'nickname' => $user_info->nickname,
            'profile' => profileFolder . $user_info->file_url_thumb . $user_info->f_tempname,

        );

        $respond = $this->res(200, '회원정보', $arr);
        return $this->response->setJSON($respond);
    }

    public function modify()
    {
        $division = $this->request->getHeaderLine('type');
        $token = $this->request->getHeaderLine('authtoken');

        $idx = $this->request->getGetPost('idx');

        $id = $this->request->getGetPost('id');
        $gender = $this->request->getGetPost('gender');
        $phone = $this->request->getGetPost('phone');
        $age = $this->request->getGetPost('age');
        $location = $this->request->getGetPost('location1');
        $location2 = $this->request->getGetPost('location2');
        $nickname = $this->request->getGetPost('nickname');
        $profile = $this->request->getGetPost('profile');

        if ($division == "app") {
            $user_info = $this->model->token_user_info($token);
            $idx = $user_info->idx;
        }

        $user_info = $this->model->idx_user_info($idx);

        if ($imagefile = $this->request->getFile('profile')) {
            $denyfile = array('php', 'php3', 'exe', 'cgi', 'phtml', 'html', 'htm', 'pl', 'asp', 'jsp', 'inc', 'dll', 'js', 'zip');
            if (in_array($imagefile->getExtension(), $denyfile)) {
                $respond = $this->res(400, '업로드불가 파일 포함.');
                return $this->response->setJSON($respond);
            }
            if ($imagefile->getExtension() == 'gif') {
                $frame = $this->AnigifCheck($imagefile->getPathName());
                if ($frame == true) {
                    $respond = $this->res(400, '업로드불가 파일 포함.');
                    return $this->response->setJSON($respond);
                }
            }
            if ($imagefile->getSize() >= 5242880) {
                $respond = $this->res(400, '업로드용량 초과파일 포함.');
                return $this->response->setJSON($respond);
            }

            //기존 프로필이미지 삭제
            if ($user_info->f_tempname) {
                $getimg = $this->model->img_get($user_info->id, 1);
                foreach ($getimg as $gi) {
                    if (file_exists('/home/ntalk/files/profile/' . $user_info->file_url_real . $gi->f_tempname)) {
                        unlink('/home/ntalk/files/profile/' . $gi->file_url_real . $gi->f_tempname);
                        unlink('/home/ntalk/files/profile/' . $gi->file_url_thumb . $gi->f_tempname);
                    }
                    $this->model->img_delete($user_info->id, 1);
                }
            }

            $year = date('Y', time());
            $month = date('m', time());

            if ($imagefile->isValid() && !$imagefile->hasMoved()) {
                $newName = $imagefile->getRandomName();
                if (!is_dir(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/')) {
                    mkdir(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/', 0707, true);
                    mkdir(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/thumb/', 0707, true);
                }
                $imagefile->move(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/', $newName);
                try {
                    $image = \Config\Services::image()
                        ->withFile(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/' . $newName)
                        ->fit(320, 320, 'center')//crop
//                            ->resize(320, 320, true, 'height')
                        ->save(UPLOADPATH . 'profile' . '/' . $year . '/' . $month . '/thumb/' . $newName, 70);
                } catch (CodeIgniter\Images\ImageException $e) {
                    echo $e->getMessage();
                }
                $f_tempname = $newName;
                $f_name = $imagefile->getClientName();
                $ext = $imagefile->getExtension();
                $data = array(
                    'u_idx' => $user_info->idx,
                    'u_id' => $user_info->id,
                    'f_name' => $f_name,
                    'f_tempname' => $f_tempname,
                    'file_url_real' => $year . '/' . $month . '/',
                    'file_url_thumb' => $year . '/' . $month . '/thumb/',
                    'ext' => $ext,
                    'create_at' => date('Y-m-d H:i:s'),
                    'division' => 1,
                );
                $last_id = $this->model->img_upload($data);
            }
        } //이미지 업로드

        $getimg = $this->model->img_get($user_info->id, 1);

        $arr = array(
            'gender' => $gender,
            'phone' => $phone,
            'age' => $age,
            'location' => $location,
            'location2' => $location2,
            'nickname' => $nickname
        );

        $data = $this->model->modify($user_info->id, $arr);
        var_dump($data);
    }

    public function find_id()
    {
        $phone = $this->request->getGetPost('phone');
        $certification = $this->request->getGetPost('certification');

        if ($phone == '' || $certification == '') {
            $respond = $this->res(400, '요청정보부족');

            return $this->response->setJSON($respond);
        }

        $chk = $this->model->sms_confirm($phone, $certification);

        if ($chk) {
            $create_at = Time::parse($chk[0]->ymd . $chk[0]->hms);

            $current = Time::parse(date('Y-m-d H:i:s'));
            $diff = $create_at->difference($current);

            if ($diff->getSeconds() < 180) {
                if ($certification === $chk[0]->certification) {
                    $data = $this->model->find_id($phone, $certification);

                    if ($data == '') {
                        $respond = $this->res(406, '정보가 없습니다.', $data);

                        return $this->response->setJSON($respond);
                    } else {
//                        $this->model->sms_verification_update($phone);

                        $userlist['userlist'] = $data;

                        $respond = $this->res(200, '조회가 완료되었습니다.', $userlist);
                        return $this->response->setJSON($respond);
                    }
                } else {
                    $respond = $this->res(404, '인증실패');
                }
            } else {
                $respond = $this->res(500, '인증시간을 초과하였습니다.');
            }

            return $this->response->setJSON($respond);
        } else {
            $respond = $this->res(404, '인증실패');
            return $this->response->setJSON($respond);
        }
    }

    /**
     * SMS 인증 제외 비번변경 API
     * @return \CodeIgniter\HTTP\Response
     */
    public function mypage_pass()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');

        $password = $this->request->getGetPost('password');
        $password_confirm = $this->request->getGetPost('password_confirm');

        if ($password !== $password_confirm) {
            $respond = $this->res(404, '비밀번호가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        }

        $user_info = $this->user_authentication($token, $division);

        if (!$user_info) {
            $respond = $this->res(404, '일치하는 회원정보가 없습니다.');
            return $this->response->setJSON($respond);
        }

        $result = $this->model->update_pass($user_info->id, password_hash($password, PASSWORD_DEFAULT));

        if ($result == 'success') {
            $respond = $this->res(200, '비밀번호가 변경되었습니다.');
            return $this->response->setJSON($respond);
        } else {
            $respond = $this->res(500, '서버오류.');
            return $this->response->setJSON($respond);
        }
    }

    /**
     * 비밀번호 변경  API
     * @return \CodeIgniter\HTTP\Response
     */
    public function find_pass()
    {
        $id = $this->request->getGetPost('id');
        $phone = $this->request->getGetPost('phone');
        $password = $this->request->getGetPost('password');
        $password_confirm = $this->request->getGetPost('password_confirm');
        $certification = $this->request->getGetPost('certification');

        $type = $this->request->getHeaderLine('type');

        $user_info = $this->model->user_info($id);

        if ($user_info->type) {
            $social = array("naver", "kakao", "google", "facebook");
            if (in_array($user_info->type, $social)) {
                $respond = $this->res(400, '[' . $user_info->type . '] ' . '비밀번호는 변경불가');
                return $this->response->setJSON($respond);
            }
        }

        $certification_chk = count($this->model->sms_confirm($phone, $certification));

        $chk = $this->model->sms_confirm($phone, $certification);

//        if ($chk) {
//            $create_at = Time::parse($chk[0]->ymd.$chk[0]->hms);
//
//            $current = Time::parse(date('Y-m-d H:i:s'));
//            $diff = $create_at->difference($current);

//            if ($diff->getSeconds() < 180) {
        if ($password == '') {
            $respond = $this->res(400, '비밀번호를 입력하세요');
            return $this->response->setJSON($respond);
        }

        if ($password_confirm == '') {
            $respond = $this->res(400, '비밀번호 확인을 입력하세요');
            return $this->response->setJSON($respond);
        }

        if ($password !== $password_confirm) {
            $respond = $this->res(400, '비밀번호가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        }

        if (!password_regex($password)) {
            $respond = $this->res(400, '비밀번호 형식 오류');
            return $this->response->setJSON($respond);
        }

        $user = $this->model->find_pass($id, $phone, $certification);

        if ($user < 1) {
            $respond = $this->res(406, '일치하는 계정 정보가 없습니다.');
            return $this->response->setJSON($respond);
        } else {
            $this->model->update_pass($id, password_hash($password, PASSWORD_DEFAULT));

            $data = $this->model->get($id);

            $this->model->sms_verification_update($data->phone);

            if ($type == 'app') {
                $respond = $this->res(200, '비밀번호 변경이 완료되었습니다.', $data);
                return $this->response->setJSON($respond);
            } else {
                return redirect()->to('/auth');
            }
        }
//            } else {
//                $respond = $this->res(500, '인증시간을 초과하였습니다.');
//                return $this->response->setJSON($respond);
//            }
//        } else {
//            $respond = $this->res(404, '인증실패');
//            return $this->response->setJSON($respond);
//        }
    }

    /**
     * @param
     * id : 조건
     * division : 중복검사 데어터 필드값
     *
     * @return status message
     */
    public function overlap()
    {
        $id = $this->request->getGetPost('id');
        $division = $this->request->getGetPost('division');

        if ($division == 'id') {
//            if (!id_regex($id)) {
//                $respond = $this->res(400, '아이디 형식 오류');
//                return $this->response->setJSON($respond);
//            }
            $division_name = '아이디';
        } else {
            if (!nick_regex($id)) {
                $respond = $this->res(400, '특수문제 제외한 2~10자 이하로 입력 하세요.');
                return $this->response->setJSON($respond);
            }
            $division_name = '닉네임';
        }
        $count = $this->model->overlap($id, $division);
        if ((int)$count < 1) {
            $respond = $this->res(200, '사용 가능한 ' . $division_name . ' 입니다.');
        } else {
            $respond = $this->res(409, '사용할 수 없는 ' . $division_name . ' 입니다.');
        }
        return $this->response->setJSON($respond);
    }


    /**
     * SMS 문자 전송 API
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\Response
     */
    public function sms_request()
    {
        $type = $this->request->getHeaderLine('type');
        $id = $this->request->getGetPost('phone');
        $type = $this->request->getHeaderLine('type');
        $division = $this->request->getGetPost('division');
        $email = $this->request->getGetPost('email');

        $phone_limit_count = $this->model->phone_limit_count($id);

        if ($division == 0) {
            if ($phone_limit_count >= 3) {
//                if ($type == 'app') {
//                    $respond = $this->res(400, '전화번호 가입횟수 제한 초과',$division);
//                    return $this->response->setJSON($respond);
//                } else {
                $respond = $this->res(406, '휴대폰 번호 가입 횟수 초과 (3회)', $division);
                return $this->response->setJSON($respond);
//                }
            }
        }// 회원가입 인증 횟수제한

        if ($division == 1) {
            $phone_chk = $this->model->phone_chk($id);

            if (!$phone_chk) {
                $respond = $this->res(400, '가입한 전화번호가 없습니다.');
                return $this->response->setJSON($respond);
            }

        }

        if ($id == '') {
            $respond = $this->res(400, '전화번호를 입력하세요');
            return $this->response->setJSON($respond);
        }

        if ($division == 2) {
            if (isset($email)) {
                $user_info = $this->model->get_user($id);
                if (!$user_info) {
                    $respond = $this->res(400, '가입된 전화번호가 없습니다.');
                    return $this->response->setJSON($respond);
                } else {
                    $user_info = $this->model->get_user_chk($id, $email);
                    if (!$user_info) {
                        $respond = $this->res(400, '가입된 정보가 일치하지 않습니다.');
                        return $this->response->setJSON($respond);
                    }

                    $social = array('naver', 'kakao', 'facebook', 'google');

                    if (in_array($user_info->type, $social)) {
                        $respond = $this->res(400, '간편로그인 계정은 변경불가 합니다.');
                        return $this->response->setJSON($respond);
                    }
                }
            }
        }

        if (phone_regex($id)) {
            $rand_num = sprintf('%06d', rand(000000, 999999));

            $this->model->sms_verification_update($id);

            $data = $this->model->sms_request($id, $rand_num, $division);

            if ($data['last_id']) {
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

                // 링크아이디
                $LinkID = 'SELFNICK';
                // 비밀키
                $SecretKey = 'eoET39cT8DmGWf+qELLQ9r1jAquyDL1Gy/kQyOHlt9I=';
                //통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
                //STREAM 사용시에는 allow_url_fopen = on 으로 설정해야함.
                $MessagingService = new PopbillMessaging($LinkID, $SecretKey);
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
                    'snd' => '15228276',  // 발신번호
                    'sndnm' => '발신자명', // 발신자명
                    'rcv' => $id,  // 수신번호
                    'rcvnm' => '수신자성명',  // 수신자성명
                    'msg' => '[엔톡] 문자확인 인증번호 : ' . $rand_num,  // 개별 메시지 내용
                );
                try {
                    $count_chk = $this->model->sms_count($data['data']->phone);

                    if ((int)$count_chk > 10) {
                        $respond = $this->res(403, '1일 인증요청 횟수 초과입니다.');
                    } else {
                        $test = $MessagingService->SendSMS($testCorpNum, '', '', $Messages, $reserveDT, $adsYN, '', '', '', $requestNum);

                        $message = $MessagingService->GetMessages($testCorpNum, $test);


                        $resArray = array(
                            'phone' => $data['data']->phone,
                            'certification' => $data['data']->certification,
                            'create_at' => $data['data']->ymd,
                        );
                        $this->model->sms_log($data['data']->phone);
                        if ($type == 'pc') {
                            $respond = $this->res(200, "입력하신 번호로 인증 문자가 전송되었습니다", 'success', $test);
                        } else {
                            $respond = $this->res(200, "입력하신 번호로 인증 문자가 전송되었습니다", $resArray);
//                            $respond = $this->res(200, "입력하신 번호로 인증 문자가 전송되었습니다", $message);
                        }
                    }
                } catch (PopbillException $pe) {
                    $code = $pe->getCode();
                    $message = $pe->getMessage();
                    $respond = $this->res($code, $message);
                }
            } else {
                $respond = $this->res(500, 'DB 입력오류');
            }
        } else {
            $respond = $this->res(400, '전화번호 형식을 확인하세요');
        }

        return $this->response->setJSON($respond);
    }

    /**
     * @return \CodeIgniter\HTTP\Response
     *
     * @throws \Exception
     * PC SMS 인증 확인
     */
    public function sms_confirm()
    {
        $phone = $this->request->getGetPost('phone');
        $certification = $this->request->getGetPost('certification');

        if ($phone == '' || $certification == '') {
            $respond = $this->res(400, '전화번호 또는 인증번호를 확인하세요.');

            return $this->response->setJSON($respond);
        }

        $chk = $this->model->sms_confirm($phone, $certification);

        if ($chk) {
            $create_at = Time::parse($chk[0]->ymd . $chk[0]->hms);

            $current = Time::parse(date('Y-m-d H:i:s'));
            $diff = $create_at->difference($current);

            if ($diff->getSeconds() < 180) {
                if ($certification === $chk[0]->certification) {
                    $respond = $this->res(200, '인증성공');
                } else {
                    $respond = $this->res(404, '인증번호가 올바르지 않습니다.');
                }
            } else {
                $respond = $this->res(500, '인증시간을 초과하였습니다.');
            }

            return $this->response->setJSON($respond);
        } else {
            $respond = $this->res(404, '인증번호가 올바르지 않습니다.');

            return $this->response->setJSON($respond);
        }
    }

    /**
     * @param
     * token : app 인증값
     * type : app 구분값
     * img_division : 업로드 이미지 구분
     *
     * @return status message data
     */
    public function img_upload()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $img_division = $this->request->getGetPost('division');

        if ($img_division == "") {
            $respond = $this->res(406, '이미지 구분값을 입력하세요.');
            return $this->response->setJSON($respond);
        }

        //token 유저정보
        if ($division == 'app') {
            $user_info = $this->model->token_user_info($token);
            if ($user_info !== null) {
                $id = $user_info->id;
                $idx = $user_info->idx;
            } else {
                $respond = $this->res(404, '회원정보 없음.');
                return $this->response->setJSON($respond);
            }
        } else {
            if ($this->session->get('user_details')) {
                $idx = json_decode($this->session->get('user_details'))->idx;
//                $idx = $this->session->get('idx');
                $user_info = $this->model->idx_user_info($idx);
                $id = $user_info->id;
                $idx = $user_info->idx;
            } else {
                $user_info = $this->model->idx_user_info($this->request->getGetPost('idx'));
            }
        }

        if ($img_division == 1) {
            $before_data = $this->model->getUserInfo($user_info->nickname);

            $before = array(
                'nickname' => $before_data->nickname,
                'location' => $before_data->location,
                'location2' => $before_data->location2,
                'gender' => $before_data->gender,
                'age' => $before_data->age,
                'real' => profileFolder . $before_data->file_url_real . $before_data->f_tempname,
                'thumb' => profileFolder . $before_data->file_url_thumb . $before_data->f_tempname
            );
        }

        if ($img_division == 0) {//사진수 무제한 정책변경시 제한
            $files = $this->files->getFile($user_info->id);
        }

        if ($img_division == 1) {
            $data = $this->model->img_delete($user_info->id, $img_division);
        }

        if ($imagefile = $this->request->getFiles('img[]')) {
            if ($idx == '') {
                $respond = $this->res(400, '잘못된 요청입니다.');

                return $this->response->setJSON($respond);
            }

            $denyfile = array('php', 'php3', 'exe', 'cgi', 'phtml', 'html', 'htm', 'pl', 'asp', 'jsp', 'inc', 'dll', 'js');

            //확장자체크
            foreach ($imagefile['img'] as $img) {
                if (in_array($img->getExtension(), $denyfile)) {
                    $respond = $this->res(400, '업로드불가 파일 포함.');
                    return $this->response->setJSON($respond);
                }

//                var_dump($this->AnigifCheck($img->getPathName()));
                if ($img->getExtension() == 'gif' || $this->AnigifCheck($img->getPathName())) {
                    $frame = $this->AnigifCheck($img->getPathName());
                    if ($frame == true) {
                        $respond = $this->res(400, '업로드불가 파일 포함.');
                        return $this->response->setJSON($respond);
                    }
                }

                if ($img->getSize() >= 10485760) {
                    $respond = $this->res(400, '업로드용량 초과파일 포함.');
                    return $this->response->setJSON($respond);
                }
            }

            $year = date('Y', time());
            $month = date('m', time());

            $divisionArr = array();
            foreach ($imagefile['img'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getRandomName();

                    if ($img_division == 1) {
                        $folder = 'profile';
                    } else {
                        $folder = 'gallery';
                    }

                    if (!is_dir(UPLOADPATH . $folder . '/' . $year . '/' . $month . '/')) {
                        mkdir(UPLOADPATH . $folder . '/' . $year . '/' . $month . '/', 0777, true);
                        mkdir(UPLOADPATH . $folder . '/' . $year . '/' . $month . '/thumb/', 0777, true);
                    }
                    $img->move(UPLOADPATH . $folder . '/' . $year . '/' . $month . '/', $newName);

                    try {
                        $image = \Config\Services::image()
                            ->withFile(UPLOADPATH . $folder . '/' . $year . '/' . $month . '/' . $newName)
                            ->fit(320, 320, 'center')//crop
//                            ->resize(320, 320, true, 'height')
                            ->save(UPLOADPATH . $folder . '/' . $year . '/' . $month . '/thumb/' . $newName, 70);
                    } catch (CodeIgniter\Images\ImageException $e) {
                        echo $e->getMessage();
                    }

                    $f_tempname = $newName;
                    $f_name = $img->getClientName();
                    $ext = $img->getExtension();

                    $data = array(
                        'u_idx' => $idx,
                        'u_id' => $id,
                        'f_name' => $f_name,
                        'f_tempname' => $f_tempname,
                        'file_url_real' => $year . '/' . $month . '/',
                        'file_url_thumb' => $year . '/' . $month . '/thumb/',
                        'ext' => $ext,
                        'create_at' => date('Y-m-d H:i:s'),
                        'division' => $img_division,
                    );

                    $last_id = $this->model->img_upload($data);
                }

                $nomalPic = array(
                    'f_idx' => $last_id,
                    'real' => galleryFolder . $data['file_url_real'] . $data['f_tempname'],
                    'thumb' => galleryFolder . $data['file_url_thumb'] . $data['f_tempname'],
                );

                if ($division == 0) {
                    array_push($divisionArr, $nomalPic);
                }
            }

            $result = $this->model->user_info($id);

            $returnData = array(
//                'idx' => $result->idx,
//                'id' => $result->id,
//                'token' => $result->token,
//                'gender' => $result->gender,
//                'age' => $result->age,
//                'nickname' => $result->nickname,
//                'location' => $result->location,
//                'location2' => $result->location2,
                'nickname' => $result->nickname
            );
            if ($img_division == 1) {
                if ($result->f_tempname) {
                    $returnData['profile_real'] = profileFolder . $result->file_url_real . $result->f_tempname;
                    $returnData['profile_thum'] = profileFolder . $result->file_url_thumb . $result->f_tempname;
                } else {
                    $returnData['profile_real'] = "";
                    $returnData['profile_thum'] = "";
                }

                $after_data = $this->model->getUserInfo($user_info->nickname);

                $after = array(
                    'nickname' => $after_data->nickname,
                    'location' => $after_data->location,
                    'location2' => $after_data->location2,
                    'gender' => $after_data->gender,
                    'age' => $after_data->age,
                    'real' => $returnData['profile_real'],
                    'thumb' => $returnData['profile_thum']
                );
            }

            if ($img_division == 0) {
                $returnData['files'] = $divisionArr;
                $count = $this->files->getFileCount($id, $img_division);

                $socket_data['nickname'] = $user_info->nickname;

                if ($count == 1) {
                    $list = $this->files->getFileDivision($id, $img_division);

                    $socket_data = array();
                    $socket_data['nickname'] = $user_info->nickname;

                    $arr = array();
                    foreach ($list as $lt) {
                        $file = array(
                            'f_idx' => $lt->f_idx,
                            'real' => galleryFolder . $lt->file_url_real . $lt->f_tempname,
                            'thumb' => galleryFolder . $lt->file_url_thumb . $lt->f_tempname
                        );
                        array_push($arr, $file);
                    }
                    $socket_data['files'] = $arr;
                    $cmd = 14000;
                    $this->auth_socket_io($cmd, $socket_data);
                } else {
                    $list = $this->files->getFileDivisionDesc($id, $img_division);

                    $socket_data = array();
                    $socket_data['nickname'] = $user_info->nickname;

                    $arr = array();
                    foreach ($list as $lt) {
                        $file = array(
                            'f_idx' => $lt->f_idx,
                            'real' => galleryFolder . $lt->file_url_real . $lt->f_tempname,
                            'thumb' => galleryFolder . $lt->file_url_thumb . $lt->f_tempname
                        );
                        array_push($arr, $file);
                    }
                    $socket_data['files'] = $arr;

                    $cmd = 14100;
                    $this->auth_socket_io($cmd, $socket_data);
                }
            }

            if ($img_division == 1) {
                $list = $this->files->getFileDivision($id, $img_division);

                $socket_data = array();
                $socket_data['nickname'] = $user_info->nickname;
                $socket_data['before'] = $before;
                $socket_data['after'] = $after;
                $cmd = 15000;
                $this->auth_socket_io($cmd, $socket_data);
            }

            $respond = $this->res(200, '파일업로드 완료.', $returnData);
            return $this->response->setJSON($respond);
        } else {
            $respond = $this->res(400, '파일을 선택하세요');
            return $this->response->setJSON($respond);
        }
    }

    /**
     * @param $path
     *
     * @return bool|string
     * GIF 이미지 프레임 체크
     */
    private function AnigifCheck($path)
    {
        $str = @file_get_contents($path);
        $strChk = true;
        $frameCnt = $idx = 0;
        $gifFrame = chr(hexdec('0x21')) . chr(hexdec('0xF9')) . chr(hexdec('0x04'));
        $gfLenth = strlen($gifFrame);
        if (strlen($str) <= 0) {
            return 'Not Found';
            exit;
        }
        while ($strChk == true) {
            if (strpos($str, $gifFrame, $idx)) {
                ++$frameCnt;
                $idx = strpos($str, $gifFrame, $idx) + $gfLenth;
                $strChk = true;
            } elseif ($frameCnt >= 3 || !strpos($str, $gifFrame, $idx)) {
                break;
            }
        }

        return $frameCnt > 1 ? true : false;
    }

    /**
     * @param
     * token : app 인증값
     * type : app 구분값
     * id : 회원 ID
     * phone : 회원 전화번호
     * img_division : 업로드 위치 구분
     *
     * @return status message
     */
    public function img_delete()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $img_division = $this->request->getGetPost('division');

        $remove = $this->request->getGetPost('remove');

        if ($division == 'app') {
            $user_info = $this->model->token_user_info($token);
            if ($user_info !== null) {
                $id = $user_info->id;
                $idx = $user_info->idx;
            } else {
                $respond = $this->res(404, '회원정보 없음.');
                return $this->response->setJSON($respond);
            }
        } else {
            if ($this->session->get('user_details')) {
                $idx = json_decode($this->session->get('user_details'))->idx;
                $user_info = $this->model->idx_user_info($idx);
                $id = $user_info->id;
                $idx = $user_info->idx;
            } else {
                $user_info = $this->model->idx_user_info($this->request->getGetPost('idx'));
            }
        }

        if (!$user_info) {
            $respond = $this->res(404, '사용자 정보가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        } else {
            $getimg = $this->model->img_get($user_info->id, $img_division);


            if ($getimg == null) {
                $respond = $this->res(406, '해당파일이 존재하지 않습니다.');
                return $this->response->setJSON($respond);
            } else {

                if ($img_division == 0) {
                    $img_list = $this->files->getDeleteImgList($remove, $user_info->id, $img_division);

                    foreach ($img_list as $gi) {
                        if (file_exists('/home/ntalk/files/gallery/' . $gi->file_url_real . $gi->f_tempname)) {
                            unlink('/home/ntalk/files/gallery/' . $gi->file_url_real . $gi->f_tempname);
                            unlink('/home/ntalk/files/gallery/' . $gi->file_url_thumb . $gi->f_tempname);
                        }
                    }
                    $data = $this->model->gallery_img_delete($remove, $user_info->id);
                } else {
                    $before_data = $this->model->getUserInfo($user_info->nickname);

                    $before = array(
                        'nickname' => $before_data->nickname,
                        'location' => $before_data->location,
                        'location2' => $before_data->location2,
                        'gender' => $before_data->gender,
                        'age' => $before_data->age,
                        'real' => profileFolder . $before_data->file_url_real . $before_data->f_tempname,
                        'thumb' => profileFolder . $before_data->file_url_thumb . $before_data->f_tempname
                    );

                    foreach ($getimg as $gi) {
                        if (file_exists('/home/ntalk/files/profile/' . $user_info->file_url_real . $gi->f_tempname)) {
                            unlink('/home/ntalk/files/profile/' . $gi->file_url_real . $gi->f_tempname);
                            unlink('/home/ntalk/files/profile/' . $gi->file_url_thumb . $gi->f_tempname);
                        }
                        $data = $this->model->img_delete($user_info->id, $img_division);
                    }
                }

                if ($img_division == 0) {
                    $count = $this->files->getFileCount($id, $img_division);

                    if ($count == 0) {
                        $cmd = 14200;
                        $arr = array('nickname' => $user_info->nickname);
                        $this->auth_socket_io($cmd, $arr);
                    } else {
                        $list = $this->files->getFileDivision($id, $img_division);

                        $socket_data['nickname'] = $user_info->nickname;
                        $arr = array();
                        foreach ($list as $lt) {
                            $file = array(
                                'nickname' => $user_info->nickname,
                                'f_idx' => $lt->f_idx,
                                'real' => galleryFolder . $lt->file_url_real . $lt->f_tempname,
                                'thumb' => galleryFolder . $lt->file_url_thumb . $lt->f_tempname
                            );
                            array_push($arr, $file);
                        }
                        $socket_data['files'] = $arr;
                        $cmd = 14100;
                        $this->auth_socket_io($cmd, $socket_data);
                    }
                }

                if ($img_division == 1) {
                    $list = $this->files->getFileDivision($id, $img_division);

                    $arr = array();
                    foreach ($list as $lt) {
                        $file = array(
                            'nickname' => $user_info->nickname,
                            'f_idx' => $lt->f_idx,
                            'real' => galleryFolder . $lt->file_url_real . $lt->f_tempname,
                            'thumb' => galleryFolder . $lt->file_url_thumb . $lt->f_tempname
                        );
                        array_push($arr, $file);
                    }

                    $after_data = $this->model->getUserInfo($user_info->nickname);

                    $after = array(
                        'nickname' => $after_data->nickname,
                        'location' => $after_data->location,
                        'location2' => $after_data->location2,
                        'gender' => $after_data->gender,
                        'age' => $after_data->age,
                        'real' => profileFolder . $after_data->file_url_real . $after_data->f_tempname,
                        'thumb' => profileFolder . $after_data->file_url_thumb . $after_data->f_tempname
                    );
                    $socket_data['before'] = $before;
                    $socket_data['after'] = $after;

                    $cmd = 15000;
                    $this->auth_socket_io($cmd, $socket_data);
                }

                if ($data == 'success') {
                    $respond = $this->res(200, '삭제되었습니다.');
                    return $this->response->setJSON($respond);
                } else {
                    $respond = $this->res(500, '서버오류.', $data);
                    return $this->response->setJSON($respond);
                }
            }
        }
        $this->model->img_delete($id, $img_division);
    }

    public function likeSubmit($token, $like_id)
    {
        if ($token) {
            $user_info = $this->model->token_user_info($token);
        } else {
            $idx = $this->session->get('idx');
            $user_info = $this->model->idx_user_info($idx);
        }

        $year = date('Y', time());
        $month = date('m', time());
        $day = date('d', time());
        $chk = [
            'id' => $user_info->id,
            'like_id' => $like_id,
            'year' => $year,
            'month' => $month,
            'day' => $day
        ];
        if ($this->session->get('nickname') !== '겜성') {
            $count = $this->model->likeSubmit_chk($chk);

            if ($count < 1) {
                $data = array(
                    'id' => $user_info->id,
                    'like_id' => $like_id,
                    'year' => $year,
                    'month' => $month,
                    'day' => $day
                );
                $result = $this->model->likeSubmit($data);
                return $result;
            }
        }
    }

    public function ranking()
    {
        $data = [
            (int)date('Y', time()),
            (int)date('m', time()),
            (int)date('d', time()) - 1
        ];
        $result['rank'] = $this->model->ranking($data);

        if ($result) {
            $respond = $this->res(200, '랭킹 조회', $result);
            return $this->response->setJSON($respond);
        } else {
            $respond = $this->res(404, '랭킹 데이터가 없습니다.', $result);
            return $this->response->setJSON($respond);
        }
    }

    /**
     * @param
     * token : app 인증값
     * type : app 구분값
     * nickname : 변경 닉네임
     *
     * @return status message
     */
    public function nickname_change()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $nickname = $this->request->getGetPost('nickname');

        if ($division == 'app') {
            $nick_change = $this->model->nickname_change($nickname, $token);

            if ($nick_change[0] == 'fail') {
                $respond = $this->res(500, '변경 서버오류.');

                return $this->response->setJSON($respond);
            } else {
                $data = array(
                    'oldnick' => $nick_change['oldnick'],
                    'newnick' => $nick_change['newnick'],
                );

//                $images = $this->mongo_m->nick_change_images($data['oldnick'], $data['newnick']);
                $joinRoom = $this->mongo_m->nick_change_join_room($data['oldnick'], $data['newnick']);
//                $reports = $this->mongo_m->nick_change_reports($data['oldnick'], $data['newnick']);
//                $room = $this->mongo_m->nick_change_room($data['oldnick'], $data['newnick']);
//                $history = $this->mongo_m->nick_change_history($data['oldnick'], $data['newnick']);
//                $firebase = $this->mongo_m->nick_change_firebase($data['oldnick'], $data['newnick']);
//                $profiles = $this->mongo_m->nick_change_profiles($data['oldnick'], $data['newnick']);


                $respond = $this->res(200, 'test', $joinRoom);
                return $this->response->setJSON($respond);
            }
        } else {
            $data = array(
                'oldnick' => $this->request->getGetPost('oldnick'),
                'newnick' => $this->request->getGetPost('newnick'),
            );
            $joinRoom = $this->mongo_m->nick_change_join_room($data['oldnick'], $data['newnick']);
            $respond = $this->res(200, 'test', $joinRoom);
            return $this->response->setJSON($respond);
        }
    }

    /**
     * @param
     * id : 회원 ID
     * type : 회원가입 구분
     *
     * @return status message data
     */
    public function user_delete()
    {
        $id = $this->request->getGetPost('id');
        $user_info = $this->model->get($id);
        $type = $user_info->type;

        $social = array('naver', 'google', 'kakao', 'facebook');
        if (in_array($type, $social)) {
            //social unlink
        }

        $get_fiile_list = $this->model->img_all($id);
        $this->model->user_delete($id);
    }

    public function phone_count_delete()
    {
        $email = $this->request->getGetPost('email');
        $this->model->del_phone_log($email);
    }

    public function insert_cutout()
    {
        $hash = $this->request->getPost('hash');
        $cut_nickname = $this->request->getPost('nickname');

        $id = $this->model->getHashId($hash)->id;
        $cut_id = $this->model->getUserInfo($cut_nickname)->uid;

        $chk = $this->model->cutout_chk($id, $cut_id);

        $data = array('id' => $id,
            'cut_id' => $cut_id,
            'create_at' => date('Y-m-d H:i:s')
        );

        $result = $this->model->insert_cutout($data);

        var_dump($result);
    }

    /**
     * @param
     * code : http status
     * message : alert 문
     * data : return data
     *
     * @return status message data
     */
    public function res($code, $message, $data = array())
    {
        switch ($code) {
            case 400:
                $this->response->setStatusCode(400);
                break;
            case 403:
                $this->response->setStatusCode(403);
                break;
            case 404:
                $this->response->setStatusCode(404);
                break;
            case 406:
                $this->response->setStatusCode(406);
                break;
            case 409:
                $this->response->setStatusCode(409);
                break;
            case 500:
                $this->response->setStatusCode(500);
                break;
        }
        $data = (object)$data;
        $res = array(
            'code' => (int)$code,
            'message' => $message,
            'data' => $data,
        );

        return $res;
    }

    /**
     *merge sort test.
     */
    public function sort()
    {
        $pw = password_hash('1234qwer', PASSWORD_BCRYPT);
        $this->model->admin_login($pw);

        return $pw;

        $year = date('Ymd', time());
        $month = date('m', time());
        $day = date('d', time());

        return $year;

        $test_array = $this->request->getGetPost('ee');

        return var_dump(merge_sort($test_array));
    }

    public function auth_socket_io($cmd, $data)
    {
        $client = new Client(new Version2X('https://ntalk.me:2580', ['context' => ['ssl' => ['verify_peer_name' => false, 'verify_peer' => false]]]));
        $client->initialize();

        try {
            $client->emit('SUBMIT',
                [
                    'cmd' => $cmd,
                    'data' => $data
                ]
            );
            $client->close();
        } catch (\ServerConnectionFailureException $e) {
            echo 'Server Connection Failure!!';
        }
    }

    //--------------------------------------------------------------------
}
