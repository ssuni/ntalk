<?php namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Jsonwebtotokens;
use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Timeline_m;
use App\Models\Api\Files_m;
use App\Models\Api\Oauth_m;
use CodeIgniter\I18n\Time;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;


class Timeline extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper(['function','alert']);

        $this->request = \Config\Services::request();
        $this->timeline = new Timeline_m();
        $this->files = new Files_m();
        $this->oauth = new Oauth_m();
    }

    public function modeltest()
    {
        $timeLineModel = new Timeline_m();

        $list = $this->timeline->find();

        var_dump($list);
    }

    public function curltest()
    {
        $client = \Config\Services::curlrequest();
        $response = $client->request('POST','https://haon.ntalk.me:8443/fcm_send', [
            'allow_redirects'  =>  false ,
            'verify' => false,
                'form_params' => [
                'os' => 'android',
                'fcm_token' => json_encode(array("cBqmTurEkk4:APA91bE0xxeXrF04Xer2sqDiEpyb7XXqSVmq7czoEmrW6HgPmp0SOz9rvsWhMU381kvBiIDHI8SYJ1H_RvrcaTr9TaiiBAn_7hTdNTlbpmUtrEE-W1ouJ7HKf42XupULY2g8HHhAmW-6")),
                'data' => json_encode(array('cmd'=>200,
                                    'data' => array('room' => '1234567890', 'from' => 'mobile@selfnick.com',
                                    'to' => 'mobile2@selfnick.com', 'os' => 'android',
                                    'data' => array('text' => 'PHP curl TEST', 'image' => null))), JSON_FORCE_OBJECT),
                'notification_msg' => 'PHP curl TEST'
            ]
        ]);
        print_r($response->getBody());
    }

    public function timeline_lists()
    {
        $this->session = \Config\Services::session();

        $token = $this->request->getHeaderLine('authtoken');
        $getpage = $this->request->getGetPost('page');
        $type = $this->request->getHeaderLine('type');

        //검색조
        $location = $this->request->getGetPost('location1');
        $location2 = $this->request->getGetPost('location2');
        $minAge = $this->request->getGetPost('minAge');
        $maxAge = $this->request->getGetPost('maxAge');
        $gender = $this->request->getGetPost('gender');
        $keyword = $this->request->getGetPost('keyword');

        //지인차단 개발 ?
        $uid = $this->request->getGetPost('uid');
        $block_list = $this->oauth->block_list($uid);

        //일반차단
//        if($token && $this->session->get('id')) {
//            if ($type == 'app') {
//                $user_info = $this->oauth->token_user_info($token);
//            } else {
//                $user_info = $this->oauth->user_info($this->session->get('id'));
//            }
//            $cutout = $this->cutout_list($user_info->nickname);
//
//            $cutout_users = array();
//            foreach ($cutout as $co)
//            {
//                array_push($cutout_users , $co->id);
//            }
//            var_dump($cutout_users);
//            exit;
//        }
        //
        $page = $this->request->getGetPost('page');
        if($getpage == ""){
            $getpage = 1;
        }else{
            $getpage = $this->request->getGetPost('page');
        }


        $limit = 50;

        //선호 리스트 검색 회원 접근시
        $whereArr[] = "";
        $arrLike[] = "";
        if($location !== "" && $location !== null){
            $whereArr['users_info.location'] = $location;
        }

        if($location2 !== "" && $location2 !== null){
            $whereArr['users_info.location2'] = $location2;
        }

        if($minAge !== '-1' && $minAge !== '19'){
            if($minAge !== "" && $minAge !== null){
                $whereArr['users_info.age >='] = $minAge;
            }
        }

        if($maxAge !== '-1' && $maxAge !== '60'){
            if($maxAge !== "" && $maxAge !== null){
                $whereArr['users_info.age <='] = $maxAge;
            }
        }

        if($gender !== 'all') {
            if ($gender !== "" && $gender !== null) {
                $whereArr['users.gender'] = $gender;
            }
        }

//        $this->timeline->join('users','users.id = time_line.uid','left');
//        $this->timeline->join('users_info','time_line.uid = users_info.uid','left');
//        $this->timeline->join('files','time_line.uid = files.u_id and division = 1','left');
//        $this->timeline->where($whereArr);
//        $this->timeline->orderBy('time_line.create_at','desc');
//        $test =  $this->timeline->paginate($limit);
//      $test =  $this->timeline->getCompiledSelect();
//      $test =  $this->timeline->getCompiledUpdate();


        //선호 리스트 검색
        $data = [
//            'users' => $test,
            'users' => $this->timeline->select('t_idx,time_line.uid,users_info.nickname,title,comment,time_line.create_at,time_line.flocation,time_line.flocation2,
                                            users.token,time_line.fgender,users.create_at as user_create_at,
                                            users.gender,users_info.age,time_line.minAge,time_line.maxAge,

                                            files.file_url_real,files.file_url_thumb,files.f_tempname,
                                            users.hash,users_info.location,users_info.location2,
                                            ')
                                        ->join('users','users.id = time_line.uid','left')
                                        ->join('users_info','time_line.uid = users_info.uid','left')
                                        ->join('files','time_line.uid = files.u_id and division = 1','left')
                                        ->where($whereArr)

                                      ->orderBy('time_line.create_at','desc')
                                      ->paginate($limit),
//                                      ->getCompiledSelect($limit),
            'pager' => $this->timeline->pager
        ];
//        var_dump($data['users']);

        $return = array();
        for ($i=0; $i< count($data['users']); $i++){
            $return[$i] = array(
                't_idx' => $data['users'][$i]->t_idx,
                'uid' => $data['users'][$i]->uid,
                'nickname' => $data['users'][$i]->nickname,
                'title' => $data['users'][$i]->title,
                'comment' => $data['users'][$i]->comment,
                'create_at' => $data['users'][$i]->create_at,
                'token' => $data['users'][$i]->token,
                'hash' => $data['users'][$i]->hash,
                'age' => $data['users'][$i]->age,
                'gender' => $data['users'][$i]->gender,
                'location' => $data['users'][$i]->location,
                'location2' => $data['users'][$i]->location2,
                'minAge' => $data['users'][$i]->minAge,
                'maxAge' => $data['users'][$i]->maxAge,
                'fgender' => $data['users'][$i]->fgender,
                'flocation' => $data['users'][$i]->flocation,
                'flocation2' => $data['users'][$i]->flocation2,
                'user_create_at' => $data['users'][$i]->user_create_at,
            );
            if($data['users'][$i]->f_tempname) {
                $return[$i]['real'] = profileFolder . $data['users'][$i]->file_url_real . $data['users'][$i]->f_tempname;
                $return[$i]['thumb'] = profileFolder . $data['users'][$i]->file_url_thumb . $data['users'][$i]->f_tempname;
            }
            $arr = array();
            $files = $this->files->getTimelineFile($return[$i]['uid']);
            foreach ($files as $fl){
                $arrfile = array(
                        'division' => $fl->division,
                        'f_idx' => $fl->f_idx,
                        'real' => timelineFolder . $fl->file_url_real . $fl->f_tempname,
                        'thumb' => timelineFolder . $fl->file_url_thumb . $fl->f_tempname
                );
                array_push($arr, $arrfile);
            }
            $return[$i]['files'] = $arr;

            $return[$i]['view_count'] = $this->oauth->getUserInfoCount($return[$i]['uid']);

        }

        $result['lastPage'] = (string)$data['pager']->getLastPage();
        if($getpage > $result['lastPage']){
            $result['post'] = $return;
            $result['currentPage'] = (string)$data['pager']->getCurrentPage();
            $result['limit'] = $limit;
            $respond =  $this->res(404, '자료가 없습니다.',$result);
            return $this->response->setJSON($respond);
        }

        if($getpage == $result['lastPage'])
        {
            $result['post'] = $return;
            $result['currentPage'] = (string)$data['pager']->getCurrentPage();
            $result['limit'] = $limit;

            $respond =  $this->res(200, '마지막 페이지 입니다.',$result);
            return $this->response->setJSON($respond);
        }

        $page = explode('=',$data['pager']->getNextPageURI());
        $nextPage = $page[1];
        $result['post'] = $return;

        $result['currentPage'] = (string)$data['pager']->getCurrentPage();
        $result['nextPage'] = $nextPage;
        $result['limit'] = $limit;

        $respond =  $this->res(200, 'success',$result);
        return $this->response->setJSON($respond);
    }

    /**
     * 타임라인 글등록
     * @return 등록글 정보,직전 등록글 번호
     */
    public function timeline_insert()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');

        $comment = $this->request->getGetPost('comment');
        $title = $this->request->getGetPost('title');
        $minAge = $this->request->getGetPost('minAge');
        $maxAge = $this->request->getGetPost('maxAge');
        $flocation = $this->request->getGetPost('location1');
        $flocation2 = $this->request->getGetPost('location2');
        $fgender = $this->request->getGetPost('gender');

//        var_dump($this->request->getGetPost());
//        exit;

        if($title == ""){
            $respond =  $this->res(400, '글제목을 입력하세요.');
            return $this->response->setJSON($respond);
        }
        if($comment == ""){
            $respond =  $this->res(400, '게시글을 입력하세요.');
            return $this->response->setJSON($respond);
        }

        $comment_count = mb_strlen($comment, 'utf-8');

        if($comment_count > 10000){
            $respond =  $this->res(406, '타임라인 글자수 제한.');
            return $this->response->setJSON($respond);
        }

        $user_info = $this->user_authentication($token, $division);


//        if($division == 'app') {
//            $user_info = $this->oauth->token_user_info($token);
//        }else{
//            if($this->session->get('user_details')) {
//                $idx = json_decode($this->session->get('user_details'))->idx;
//                $user_info = $this->oauth->idx_user_info($idx);
//            }else{
//                $user_info = $this->oauth->idx_user_info($this->request->getGetPost('idx'));
//            }
//        }

        if(!$user_info){
            $respond =  $this->res(404, '사용자 정보가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        }

        //정책확인 필요
        $timeline_tr = $this->timeline->timeline_tr($user_info->id); // 열갯수
//        if ($timeline_tr >= 1) {
//            $timeline_presence = $this->timeline->timeline_presence($user_info->id);
//
//            $create_at = Time::parse($timeline_presence->create_at);
//            $current = Time::parse(date('Y-m-d H:i:s'));
//            $diff = $create_at->difference($current);
//            if ($diff->getMinutes() <= 360) {
//                $respond = $this->res(406, '재등록 가능시간이 아닙니다.', array('diff' => $diff->getMinutes()));
//                return $this->response->setJSON($respond);
//            } else {
//                $getTimeline = $this->timeline->get_timeline($user_info->id);
//
//                var_dump($getTimeline->t_idx);
//                exit;
//
//                $this->timeline->delete_timeline($user_info->id, $getTimeline);
//            }
//        }

        $id = $user_info->id;
        $gender = $user_info->gender;


        if($user_info->age == ""){
            $age = 0;
        }else{
            $age = $user_info->age;
        }
        $nickname = $user_info->nickname;
        $location = $user_info->location;
        $location2 = $user_info->location2;

        $date = date("Y-m-d H:i:s");

        $data = array(
            'uid'       => $id,
            'age'       => $age,
            'gender'    => $gender,
            'title'    => $title,
            'comment'   => $comment,
            'minAge'   => $minAge,
            'maxAge'   => $maxAge,
            'comment'   => $comment,
            'flocation'   => $flocation,
            'flocation2'   => $flocation2,
            'fgender'   => $fgender,
            'create_at' => $date
        );
        $timeline_img_all = $this->timeline->allDeleteImg($user_info->id);

        $remove = array();
        foreach ($timeline_img_all as $ta)
        {
            $f_idx = $ta->f_idx;
            array_push($remove,$f_idx);
        }
        if($remove) {
            $result = $this->timeline->timeline_img_delete($remove, $user_info->id);

            if ($result !== 'success') {
                $respond = $this->res(500, '서버오류.');
                return $this->response->setJSON($respond);
            }
            foreach ($timeline_img_all as $il) {
                if (file_exists('/home/ntalk/files/timeline/' . $il->file_url_real . $il->f_tempname)) {
                    unlink('/home/ntalk/files/timeline/' . $il->file_url_real . $il->f_tempname);
                    unlink('/home/ntalk/files/timeline/' . $il->file_url_thumb . $il->f_tempname);
                }
            }
        }
        $timeline_presence = $this->timeline->timeline_presence($user_info->id); //기존 타임라인 글
        if($timeline_presence) {
            $this->timeline->delete_timeline($user_info->id, $timeline_presence->t_idx);
        }
        $last_id = $this->timeline->insert_timeline($data); //타임라인 글등록

        if ($imagefile = $this->request->getFiles('img[]')) {

            $denyfile = array('php', 'php3', 'exe', 'cgi', 'phtml', 'html', 'htm', 'pl', 'asp', 'jsp', 'inc', 'dll', 'js');

            //확장자체크
            foreach ($imagefile['img'] as $img) {
                if (in_array($img->getExtension(), $denyfile)) {
                    $respond = $this->res(400, '업로드불가 파일 포함.');

                    return $this->response->setJSON($respond);
                }

                if ($img->getExtension() == 'gif') {
                    $frame = $this->AnigifCheck($img->getPathName());
                    if ($frame == true) {
                        $respond = $this->res(400, '업로드불가 파일 포함.');

                        return $this->response->setJSON($respond);
                    }
                }

                if ($img->getSize() >= 5242880) {
                    $respond = $this->res(400, '업로드용량 초과파일 포함.');

                    return $this->response->setJSON($respond);
                }
            }

            $year = date('Y', time());
            $month = date('m', time());

            foreach ($imagefile['img'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getRandomName();

                    if (!is_dir(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/')) {
                        mkdir(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/', 0707, true);
                        mkdir(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/thumb/', 0707, true);
                    }
                    $img->move(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/', $newName);

                    try {
                        $image = \Config\Services::image()
                            ->withFile(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/' . $newName)
                            ->fit(320, 320, 'center')//crop
//                            ->resize(320, 320, true, 'height')
                            ->save(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/thumb/' . $newName, 70);
                    } catch (CodeIgniter\Images\ImageException $e) {
                        echo $e->getMessage();
                    }

                    $f_tempname = $newName;
                    $f_name = $img->getClientName();
                    $ext = $img->getExtension();

                    $data = array(
                        'u_idx' => $user_info->idx,
                        'u_id' => $id,
                        'f_name' => $f_name,
                        'f_tempname' => $f_tempname,
                        'file_url_real' => $year . '/' . $month . '/',
                        'file_url_thumb' => $year . '/' . $month . '/thumb/',
                        'ext' => $ext,
                        'create_at' => date('Y-m-d H:i:s'),
                        'division' => 2,
                    );
                    $this->oauth->img_upload($data);
                }
            }
        }

        if($last_id){
            $prev = $this->timeline->prev($user_info->id,$last_id);

            $files = $this->timeline->allDeleteImg($user_info->id);
            $imgArr = array();
            foreach ($files as $fl) {
                $upImg = array('real' => timelineFolder.$fl->file_url_real.$fl->f_tempname,
                                'thumb' => timelineFolder.$fl->file_url_thumb.$fl->f_tempname,
                    );
                array_push($imgArr,$upImg);
            }

            $data['content_id'] = (string)$last_id;
            $data['nickname'] = $nickname;
            $data['gender'] = $gender;
            $data['age'] = $age;
            $data['location'] = $location;
            $data['t_idx'] = $last_id;
            $data['prev'] = $prev;
            $data['files'] = $imgArr;

            if($user_info->f_tempname) {
                $real = profileFolder.$user_info->file_url_real.$user_info->f_tempname;
                $thumb = profileFolder.$user_info->file_url_thumb.$user_info->f_tempname;
            }else{
                $real = null;
                $thumb = null;
            }

            $socket_data = array(
                't_idx' => $last_id,
                'uid' => $user_info->id,
                'nickname' => $user_info->nickname,
                'title' => $title,
                'comment' => $comment,
                'create_at' => $date,
                'age' => $user_info->age,
                'gender' => $user_info->gender,
                'location' => $user_info->location,
                'location2' => $user_info->location2,
                'minAge' => $minAge,
                'maxAge' => $maxAge,
                'fgender' => $fgender,
                'flocation' => $flocation,
                'flocation2' => $flocation2,
                'real' => $real,
                'thumb' => $thumb,
                'files' => $imgArr
            );

            $cmd = 12000;
            $socker_curl = $this->timeline_socket_io($cmd,$socket_data);

            if($division == 'app') {
                if($socker_curl) {
                    $respond = $this->res(500, '소켓오류', $socker_curl);
                    return $this->response->setJSON($respond);
                }else{
                    $respond = $this->res(200, '등록성공', $socket_data);
                    return $this->response->setJSON($respond);
                }
            }else{
                if($socker_curl) {
                    $respond = $this->res(500, '소켓오류', $socker_curl);
                    return $this->response->setJSON($respond);
                }else{
                    return redirect()->to('/main');
                }
            }
        }else{
            $respond = $this->res(500, '등록오류');
            return $this->response->setJSON($respond);
        }
    }

    public function timeline_insert_cancel()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $f_idx = $this->request->getGetPost('f_idx[]');

        var_dump($f_idx);
    }
    public function timeline_image_upload_arr()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $img_division = $this->request->getGetPost('division');
        if($img_division == ""){
            $img_division = 2;
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
            $id = $this->session->get('id');
            $idx = $this->session->get('3');

            $id = 'sonminsoon@naver.com';
            $idx = '89';
        }

        if ($imagefile = $this->request->getFiles('img[]')) {
            if ($idx == '') {
                $respond = $this->res(400, '잘못된 요청입니다.');
                return $this->response->setJSON($respond);
            }
            $denyfile = array('php', 'php3', 'exe', 'cgi', 'phtml', 'html', 'htm', 'pl', 'asp', 'jsp', 'inc', 'dll', 'js', 'zip');

            //확장자체크
            foreach ($imagefile['img'] as $img) {
                if (in_array($img->getExtension(), $denyfile)) {
                    $respond = $this->res(400, '업로드불가 파일 포함.');

                    return $this->response->setJSON($respond);
                }

                if ($img->getExtension() == 'gif') {
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

            $arr = array();

            foreach ($imagefile['img'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getRandomName();

                    if (!is_dir(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/')) {
                        mkdir(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/', 0707, true);
                        mkdir(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/thumb/', 0707, true);
                    }
                    $img->move(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/', $newName);

                    try {
                        $image = \Config\Services::image()
                            ->withFile(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/'.$newName)
                            ->fit(320, 320, 'center')//crop
                            ->save(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/thumb/'.$newName, 70);
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
                        'file_url_real' => $year.'/'.$month.'/',
                        'file_url_thumb' => $year.'/'.$month.'/thumb/',
                        'ext' => $ext,
                        'create_at' => date('Y-m-d H:i:s'),
                        'division' => $img_division,
                    );
                    $last_id = $this->oauth->img_upload($data);

                    $getUser = $this->oauth->user_info($id);
                    $getImg = $this->timeline->timeline_img_get($last_id);

                    $returnData = array(
                        'idx' => $getUser->idx,
                        'id' => $getUser->id,
                        'token' => $getUser->token,
                        'gender' => $getUser->gender,
                        'age' => $getUser->age,
                        'nickname' => $getUser->nickname,
                        'location' => $getUser->location,
                        'location2' => $getUser->location2,
                        'f_idx' => $getImg->f_idx,
                        'profile_real' => 'http://files.ntalk.me/timeline/'.$getImg->file_url_real.$getImg->f_tempname,
                        'profile_thum' => 'http://files.ntalk.me/timeline/'.$getImg->file_url_thumb.$getImg->f_tempname,
                        'tag' => $getUser->tag,
                    );
                    array_push($arr,$returnData);
                } else {
                    $respond = $this->res(400, '잘못된 요청입니다.');
                    return $this->response->setJSON($respond);
                }
            }//foreach

            $respond = $this->res(200, '파일업로드 완료.', $arr);
            return $this->response->setJSON($respond);

        } else {
            $respond = $this->res(400, '파일을 선택하세요');
            return $this->response->setJSON($respond);
        }
    }

    public function timeline_image_upload()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $img_division = $this->request->getGetPost('division');
        if($img_division == ""){
            $img_division = 2;
        }

        //token 유저정보
        if ($division == 'app') {
            $user_info = $this->oauth->token_user_info($token);
            if ($user_info !== null) {
                $id = $user_info->id;
                $idx = $user_info->idx;
            } else {
                $respond = $this->res(404, '회원정보 없음.');
                return $this->response->setJSON($respond);
            }
        } else {
            if($this->session->get('user_details')) {
                $idx = json_decode($this->session->get('user_details'))->idx;
                $user_info = $this->oauth->idx_user_info($idx)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             ;
            }else{
                $user_info = $this->oauth->idx_user_info($this->request->getGetPost('idx'));
            }
        }

        $timeline_img_count = $this->timeline->timeline_img_count($user_info->id);

        //타임라인 활성 이미지 3개 제한
//        if($timeline_img_count >= 3){
//            $respond = $this->res(406, '타임라인 이미지 3장 초과 업로드 불가.');
//            return $this->response->setJSON($respond);
//        }
        //사용자 토탈 이미지 10개
        $timeline_img_all = $this->timeline->timeline_img_all($user_info->id);

        if($timeline_img_all == 5){
            $first_img = $this->timeline->timeline_img_first($user_info->id);

            $this->timeline->timeline_img_delete($first_img->f_idx);

            if (file_exists('/home/ntalk/files/timeline/'.$first_img->file_url_real.$first_img->f_tempname)) {
                unlink('/home/ntalk/files/timeline/'.$first_img->file_url_real.$first_img->f_tempname);
                unlink('/home/ntalk/files/timeline/'.$first_img->file_url_thumb.$first_img->f_tempname);
            }
        }
        $denyfile = array('php', 'php3', 'exe', 'cgi', 'phtml', 'html', 'htm', 'pl', 'asp', 'jsp', 'inc', 'dll', 'js', 'zip');

        if ($imagefile = $this->request->getFile('image')) {

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

            if ($imagefile->getSize() >= 10485760) {
                $respond = $this->res(400, '업로드용량 초과파일 포함.');

                return $this->response->setJSON($respond);
            }
        }

        $year = date('Y', time());
        $month = date('m', time());
//        $arr = array();
//        for($i = 1; $i <=3; $i++) {
            if ($imagefile = $this->request->getFile('image')) {
                if($imagefile->isValid() && !$imagefile->hasMoved()){
                    $newName = $imagefile->getRandomName();
                    if (!is_dir(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/')) {
                        mkdir(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/', 0777, true);
                        mkdir(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/thumb/', 0777, true);
                    }
                    $imagefile->move(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/', $newName);
                    try {
                        $image = \Config\Services::image()
                            ->withFile(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/'.$newName)
                            ->fit(320, 320, 'center')//crop
//                            ->resize(320, 320, true, 'height')
                            ->save(UPLOADPATH.'timeline'.'/'.$year.'/'.$month.'/thumb/'.$newName, 70);
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
                        'file_url_real' => $year.'/'.$month.'/',
                        'file_url_thumb' => $year.'/'.$month.'/thumb/',
                        'ext' => $ext,
                        'create_at' => date('Y-m-d H:i:s'),
                        'division' => $img_division,
                    );
                    $last_id = $this->oauth->img_upload($data);

                    $getUser = $this->oauth->user_info($user_info->id);
                    $getImg = $this->timeline->timeline_img_get($user_info->id,$last_id);

                    $returnData = array(
                        'f_idx' => $last_id,
                        'profile_real' => 'http://files.ntalk.me/timeline/'.$getImg->file_url_real.$getImg->f_tempname,
                        'profile_thum' => 'http://files.ntalk.me/timeline/'.$getImg->file_url_thumb.$getImg->f_tempname
                    );

                }else{
                    $respond = $this->res(400, '잘못된 요청입니다.');
                    return $this->response->setJSON($respond);
                }
            }
            $respond = $this->res(200, '업로드 완료.',$returnData);
            return $this->response->setJSON($respond);
//        }// End for

    }

    public function timeline_image_delete()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $img_division = $this->request->getGetPost('division');
        $f_idx = $this->request->getGetPost('f_idx');

        if($img_division == ""){
            $img_division = 2;
        }

        if ($division == 'app') {
            $user_info = $this->oauth->token_user_info($token);
        } else {
            if ($this->session->get('user_details')) {
                $idx = json_decode($this->session->get('user_details'))->idx;
                $user_info = $this->oauth->idx_user_info($idx);
            } else {
                $user_info = $this->oauth->idx_user_info('53');
            }
        }
        if (!$user_info) {
            $respond = $this->res(404, '사용자 정보가 일치하지 않습니다.');

            return $this->response->setJSON($respond);
        } else {
            $getimg = $this->timeline->timeline_img_get($user_info->id, $f_idx);

            if ($getimg == null) {
                $respond = $this->res(406, '해당파일이 존재하지 않습니다.');
                return $this->response->setJSON($respond);
            } else {
                $data = $this->timeline->timeline_img_softdelete($user_info->id, $f_idx);

                if ($data == 'success') {
                    $respond = $this->res(200, '삭제되었습니다.');

                    return $this->response->setJSON($respond);
                } else {
                    $respond = $this->res(500, '서버오류.', $data);

                    return $this->response->setJSON($respond);
                }
            }
        }
        $this->oauth->img_delete($id, $img_division);
    }

    /**
     * @param $path
     *
     * @return bool|string
     *                     GIF 이미지 프레임 체크
     */
    private function AnigifCheck($path)
    {
        $str = @file_get_contents($path);
        $strChk = true;
        $frameCnt = $idx = 0;
        $gifFrame = chr(hexdec('0x21')).chr(hexdec('0xF9')).chr(hexdec('0x04'));
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

    public function timeline_socket_io($cmd,$data)
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

    public function socket_tag($respones)
    {
        $this->client = new Client(new Version2X('https://haon.ntalk.me:8443'));
        $this->client->initialize();
        $this->client->of('/rm');
        $this->client->emit('push', array('type'=>'timeline', 'response'=>$respones));
        $this->client->close();
    }

    /**
     * 타임라인 글삭제
     * @return status
     */
    public function timeline_delete()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $t_idx = $this->request->getGetPost('content_id');

        if($division == 'app'){
            $user_info = $this->oauth->token_user_info($token);
        }else{
            if($this->session->get('user_details')) {
                $idx = json_decode($this->session->get('user_details'))->idx;
                $user_info = $this->oauth->idx_user_info($idx);
            }else{
                $user_info = $this->oauth->idx_user_info('86');
            }
        }

        if(!$user_info){
            $respond =  $this->res(404, '사용자 정보가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        }

        $timeline_chk = $this->timeline->timeline_chk($user_info->id,$t_idx);

        if($timeline_chk == 1)
        {
            $img_list = $this->timeline->allDeleteImg($user_info->id);
            foreach ($img_list as $il) {
                if (file_exists('/home/ntalk/files/timeline/' . $il->file_url_real . $il->f_tempname)) {
                    unlink('/home/ntalk/files/timeline/' . $il->file_url_real . $il->f_tempname);
                    unlink('/home/ntalk/files/timeline/' . $il->file_url_thumb . $il->f_tempname);
                }
            }

            $socket_data = array(
                'nickname' => $user_info->nickname
            );
            $cmd = 13500;
            $socker_curl = $this->timeline_socket_io($cmd,$socket_data);

            $data = $this->timeline->delete_timeline($user_info->id,$t_idx);
            $respond = $this->res(200, '타임라인이 삭제되었습니다.',$data);
            return $this->response->setJSON($respond);
        }else{
            $respond = $this->res(404, '일치하는 타임라인이 없습니다.');
            return $this->response->setJSON($respond);
        }
    }

    /**
     * 타임라인 글수정
     * @return status
     */
    public function timeline_edit()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');

        $comment = $this->request->getGetPost('comment');
        $title = $this->request->getGetPost('title');
        $minAge = $this->request->getGetPost('minAge');
        $maxAge = $this->request->getGetPost('maxAge');
        $flocation = $this->request->getGetPost('location1');
        $flocation2 = $this->request->getGetPost('location2');
        $fgender = $this->request->getGetPost('gender');
        $remove = $this->request->getGetPost('remove');


        if ($division == 'app') {
            $user_info = $this->oauth->token_user_info($token);
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
                $id = json_decode($this->session->get('user_details'))->id;
                $user_info = $this->oauth->idx_user_info($idx);
            } else {
                $user_info = $this->oauth->idx_user_info('53');
            }
        }

        $timeline_presence = $this->timeline->timeline_presence($user_info->id); //기존 타임라인 글
        $create_at = Time::parse($timeline_presence->create_at);
        $current = Time::parse(date('Y-m-d H:i:s'));

        $diff = $create_at->difference($current);

//        if($diff->getMinutes() <= 3600) {
//            $respond = $this->res(406, '재등록 가능시간이 아닙니다.', array('diff' => $diff->getMinutes()));
//            return $this->response->setJSON($respond);
//        }
        if (!$user_info) {
            $respond = $this->res(406, '사용자 정보가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        }

        if ($remove)//삭제 값
        {
            $imagefile = $this->request->getFiles('img[]');

            if ($imagefile) {
                if ($this->timeline->timeline_img_count($user_info->id) - count($remove) + count($imagefile) > 5) {
                    $respond = $this->res(409, '타임라인 이미지 등록갯수 오류.');
                    return $this->response->setJSON($respond);
                }
            }

            $img_list = $this->timeline->getDeleteImg($remove, $user_info->id);

            $result = $this->timeline->timeline_img_delete($remove, $user_info->id);

            if ($result !== 'success') {
                $respond = $this->res(500, '서버오류.');
                return $this->response->setJSON($respond);
            }
            foreach ($img_list as $il) {
                if (file_exists('/home/ntalk/files/timeline/' . $il->file_url_real . $il->f_tempname)) {
                    unlink('/home/ntalk/files/timeline/' . $il->file_url_real . $il->f_tempname);
                    unlink('/home/ntalk/files/timeline/' . $il->file_url_thumb . $il->f_tempname);
                }
            }
        }
//            if ($this->timeline->timeline_img_count($user_info->id) >= 5) {
//                $respond = $this->res(409, '타임라인 이미지 등록갯수 오류.');
//                return $this->response->setJSON($respond);
//            }

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

                    if ($img->getExtension() == 'gif') {
                        $frame = $this->AnigifCheck($img->getPathName());
                        if ($frame == true) {
                            $respond = $this->res(400, '업로드불가 파일 포함.');

                            return $this->response->setJSON($respond);
                        }
                    }

                    if ($img->getSize() >= 5242880) {
                        $respond = $this->res(400, '업로드용량 초과파일 포함.');

                        return $this->response->setJSON($respond);
                    }
                }

                $year = date('Y', time());
                $month = date('m', time());

                foreach ($imagefile['img'] as $img) {
                    if ($img->isValid() && !$img->hasMoved()) {
                        $newName = $img->getRandomName();

                        if (!is_dir(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/')) {
                            mkdir(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/', 0707, true);
                            mkdir(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/thumb/', 0707, true);
                        }
                        $img->move(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/', $newName);

                        try {
                            $image = \Config\Services::image()
                                ->withFile(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/' . $newName)
                                ->fit(320, 320, 'center')//crop
//                            ->resize(320, 320, true, 'height')
                                ->save(UPLOADPATH . 'timeline' . '/' . $year . '/' . $month . '/thumb/' . $newName, 70);
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
                            'division' => 2,
                        );
                        $last_id = $this->oauth->img_upload($data);
                    }
                }
            }

            if ($timeline_presence) {
                $data = array('title' => $title,
                    'comment' => $comment,
                    'minAge' => $minAge,
                    'maxAge' => $maxAge,
                    'flocation' => $flocation,
                    'flocation2' => $flocation2,
                    'fgender' => $fgender,
                    'create_at' => date('Y-m-d H:i:s')
                );

                if($user_info->f_tempname) {
                    $real = profileFolder.$user_info->file_url_real.$user_info->f_tempname;
                    $thumb = profileFolder.$user_info->file_url_thumb.$user_info->f_tempname;
                }else{
                    $real = null;
                    $thumb = null;
                }

                $files = $this->timeline->allDeleteImg($user_info->id);
                $imgArr = array();
                foreach ($files as $fl) {
                    $upImg = array('real' => timelineFolder.$fl->file_url_real.$fl->f_tempname,
                        'thumb' => timelineFolder.$fl->file_url_thumb.$fl->f_tempname,
                    );
                    array_push($imgArr,$upImg);
                }

                $socket_data = array(
                    't_idx' => $timeline_presence->t_idx,
                    'uid' => $user_info->id,
                    'nickname' => $user_info->nickname,
                    'title' => $title,
                    'comment' => $comment,
                    'create_at' => $data['create_at'],
                    'age' => $user_info->age,
                    'gender' => $user_info->gender,
                    'location' => $user_info->location,
                    'location2' => $user_info->location2,
                    'minAge' => $minAge,
                    'maxAge' => $maxAge,
                    'fgender' => $fgender,
                    'flocation' => $flocation,
                    'flocation2' => $flocation2,
                    'real' => $real,
                    'thumb' => $thumb,
                    'files' => $imgArr
                );
                $cmd = 13000;
                $socker_curl = $this->timeline_socket_io($cmd,$socket_data);

                if($division == 'app') {
                    $editData = $this->timeline->edit_timeline($timeline_presence->t_idx, $data);
                    $respond = $this->res(200, '타임라인이 수정되었습니다.', $socket_data);
                    return $this->response->setJSON($respond);
                }else{
                    $editData = $this->timeline->edit_timeline($timeline_presence->t_idx, $data);
                    return redirect()->to('/main');
                }
            } else {
                $respond = $this->res(404, '일치하는 타임라인이 없습니다.');
                return $this->response->setJSON($respond);
            }
      //  }//
    }

    /**
     * 타임라인 글신고
     * @return insertID
     */
    public function timeline_declaration()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');
        $t_idx = $this->request->getGetPost('t_idx');

        if($division == 'app'){
            $user_info = $this->oauth->token_user_info($token);
        }else{
            if($this->session->get('user_details')) {
                $idx = json_decode($this->session->get('user_details'))->idx;
                $user_info = $this->oauth->idx_user_info($idx);
            }
        }

        if(!$user_info){
            $respond =  $this->res(404, '사용자 정보가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        }

        //회원 pc,app 구분 수정 (app session 확인)
//        $idx = $this->request->getGetPost('idx');
//        if($idx == ""){
//            $respond = $this->res(406, '신고인 정보 오류');
//            return $this->response->setJSON($respond);
//        }

        if($t_idx == ""){
            $respond = $this->res(406, '신고 글번호 오류');
            return $this->response->setJSON($respond);
        }

        $chk = $this->timeline->declaration_chk($t_idx,$idx);

        if($chk)
        {
            $respond = $this->res(406, '이미 신고된 타임라인');
            return $this->response->setJSON($respond);
        }

        $ymd = date("Ymd");
        $hms = date('His');
        $data = array(
            't_idx' => $t_idx,
            'reporter' => $idx,
            'ymd' => $ymd,
            'hms' => $hms
        );
        $data['last_id'] = $this->timeline->declaration_timeline($data);

        if($data['last_id']){
            $respond = $this->res(200, '신고접수 완료.');
            return $this->response->setJSON($respond);
        }else{
            $respond = $this->res(500, 'DB등록오류.');
            return $this->response->setJSON($respond);
        }
    }

    /**
     * 타임라인 글작성 가능시간
     * @return minutes
     */
    public function write_time_chk($id)
    {
        $timeline_presence = $this->timeline->timeline_presence($id);
        $create_at = Time::parse($timeline_presence->create_at);
        $current = Time::parse(date('Y-m-d H:i:s'));

        $diff = $create_at->difference($current);

        if($diff->getMinutes() <= 3600) {
            $respond = $this->res(406, '재등록 가능시간이 아닙니다.', array('diff' => $diff->getMinutes()));
            return $this->response->setJSON($respond);
        }
    }

    public function timeline_presence()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');

        if($division == 'app'){
            $user_info = $this->oauth->token_user_info($token);
        }else{
            $id = $this->session->get('id');
            $user_info = $this->oauth->user_info($id);
        }
        if(!$user_info){
            $respond =  $this->res(404, '사용자 정보가 일치하지 않습니다.');
            return $this->response->setJSON($respond);
        }

        $timeline_presence = $this->timeline->timeline_presence($user_info->id);
        if(!$timeline_presence){
            $res = array(
                'code' => 200,
                'message' => '타임라인이 존재하지 않습니다.',
                'data' => null
            );
            return $this->response->setJSON($res);
        }
        $create_at = Time::parse($timeline_presence->create_at);
        $current = Time::parse(date('Y-m-d H:i:s'));
        $diff = $create_at->difference($current);

//       재등록 가능시간 체크 개발중 주석
//        if($diff->getMinutes() <= 360) {
//            $respond = $this->res(406, '재등록 가능시간이 아닙니다.', array('diff' => $diff->getMinutes()));
//            return $this->response->setJSON($respond);
//        }

        $result = $this->timeline->timeline_presence($user_info->id);


        $files = $this->timeline->allDeleteImg($user_info->id);
        $imgArr = array();
        foreach ($files as $fl) {
            $upImg = array(
                'f_idx' => $fl->f_idx,
                'real' => timelineFolder.$fl->file_url_real.$fl->f_tempname,
                'thumb' => timelineFolder.$fl->file_url_thumb.$fl->f_tempname,
            );
            array_push($imgArr,$upImg);
        }
        $result->files = $imgArr;

        if($result){
            $respond = $this->res(200, '타임라인 게시글 확인',$result);
        }else{
            $res = array(
                'code' => 200,
                'message' => '타임라인 게시글 확인',
                'data' => null
            );
            return $this->response->setJSON($res);
        }
        return $this->response->setJSON($respond);

    }

    /**
     * Api Response 형식
     * @param $code
     * @param $message
     * @param array $data
     * @return array
     */
    function res($code, $message, $data = array())
    {

        switch ($code) {
            case 400 :
                $this->response->setStatusCode(400);
                break;
            case 403 :
                $this->response->setStatusCode(403);
                break;
            case 404 :
                $this->response->setStatusCode(404);
                break;
            case 406 :
                $this->response->setStatusCode(406);
                break;
            case 409 :
                $this->response->setStatusCode(409);
                break;
            case 500 :
                $this->response->setStatusCode(500);
                break;
        }
        $data = (object)$data;
        $res = array(
            'code' => (int)$code,
            'message' => $message,
            'data' => $data
        );
        return $res;
    }
}


