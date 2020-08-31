<?php namespace App\Controllers;

use App\Models\Admin\Admin_m;
use App\Models\Api\Oauth_m;
use App\Models\Api\Timeline_m;
use App\Models\Api\Files_m;
use ElephantIO\Client;
use CodeIgniter\I18n\Time;
use ElephantIO\Engine\SocketIO\Version2X;

class Admin extends BaseController
{
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->admin_m = new Admin_m();
        $this->oauth_m = new Oauth_m();
        $this->timeline_m = new Timeline_m();
        $this->files_m = new Files_m();
        helper('function,url,alert');
    }

    public function _remap($method)
    {
        if ($_SERVER['REMOTE_ADDR'] !== "220.75.195.225" && $_SERVER['REMOTE_ADDR'] !== '49.173.15.113' && $_SERVER['REMOTE_ADDR'] !== '180.67.251.72') {
            $this->response->redirect('/login');
        }

        if (!$this->request->isAJAX() && $method !== 'login' && $method !== "index") {
            /////////POST TEST//////////
            if ($method == 'insert_block' || $method == 'sms_verification' || $method == 'block_user_chk' || $method == 'block_modal') {
                $this->$method();
                exit;
            }
            /////////POST TEST//////////
            echo view('admin/inc/admin_header_v');
            echo view('admin/inc/admin_top_v');
            echo view('admin/inc/admin_side_v');
            if ($method) {
                $this->$method();
            }
            echo view('admin/inc/admin_right_sidebar_v');
            echo view('admin/inc/admin_footer_v');
        } else {
            if ($method == 'login' || $method == "index") {
                echo view('admin/inc/admin_header_v');
                $this->$method();
                echo view('admin/inc/admin_footer_v');
            } else {
                $this->$method();
            }
        }
    }
    public function index()
    {
        $this->login();
    }

    public function block_modal()
    {
        echo view('admin/modal/block_modal.html');
    }

    public function main()
    {
        echo view('admin/admin_main_v.php');
    }

    public function login()
    {
        echo view('admin/admin_login_v');
    }

    public function do_login()
    {
        $id = $this->request->getGetPost('id');
        $password = $this->request->getGetPost('password');

        $data = $this->admin_m->admin_login($id);

//            if (password_verify($password, $data->password)) {

//        if($_SERVER['REMOTE_ADDR'] == "49.173.15.113"){
        if (password_verify($password, $data->password)) {
            $this->session->set('id', $data->id);
            echo json_encode(array('status'=>200,'message'=>'관리자님 환영합니다.','url'=>'/admin/user_lists'));
        } else {
            echo json_encode(array('status' => 404, 'message' => $password, 'url' => ''));
        }
//        }
    }

    public function logout()
    {
        $this->session->destroy();
    }

    public function getLocation()
    {
        $key = $this->request->getGetPost('name');
        $value = $this->request->getGetPost('value');
        if ($key == 'location') {
            $arr = region[$value];
            echo json_encode($arr);
        } else {
            echo null;
        }
    }

    // ==============================================================
    // 회원
    // ==============================================================
    public function user_lists()
    {
        $getpage = $this->request->getGetPost('page');
        if ($getpage == "") {
            $getpage = 1;
        }

        $keyword = $this->request->getGetPost('keyword');

        //리스트 검색 회원 접근시
        $whereArr[] = "";
        $limit = 50;

        if ($this->request->getGet('keyword')) {
            $division = $this->request->getGet('division');
            $keyword = $this->request->getGet('keyword');
        }
        $this->admin_m->select('users.id,users.phone,users.idx,users.login_ip,users.type,
                            users_info.nickname,users.gender,users_info.age,users_info.location,users_info.location2');
        $this->admin_m->join('users_info', 'users.id = users_info.uid', 'left');
        if ($keyword !== "" && $keyword !== null) {
            if ($division !== "" && $division !== null) {
                $whereArr[$division] = $keyword;
                $this->oauth_m->like($whereArr);
            } else {
                $this->admin_m->like('users.id', $keyword);
                $this->admin_m->orLike('users_info.nickname', $keyword);
                $this->admin_m->orLike('users_info.location', $keyword);
                $this->admin_m->orLike('users.phone', $keyword);
            }
        }
        $this->admin_m->orderBy('users.create_at', 'desc');
        $result = $this->admin_m->paginate($limit);

        $data = [
            'users' => $result,
            'pager' => $this->admin_m->pager
        ];


//            if($keyword !== "" && $keyword !== null){
//                if($division !== "" && $division !== null){
//                    $whereArr[$division] = $keyword;
//                    $data = [
//                        'users' => $this->admin_m->select('users.id,users.phone,users.idx,users.login_ip,users.type,
//                            users_info.nickname,users.gender,users_info.age,users_info.location,users_info.location2')
//                            ->join('users_info','users.id = users_info.uid','left')
//                            ->like($whereArr)
//                            ->orderBy('users.create_at','desc')
//                            ->paginate($limit),
//                        'pager' => $this->admin_m->pager
//                    ];
//                }else {
//                    $data = [
//                        'users' => $this->admin_m->select('users.id,users.phone,users.idx,users.login_ip,users.type,
//                            users_info.nickname,users.gender,users_info.age,users_info.location,users_info.location2')
//                            ->join('users_info','users.id = users_info.uid','left')
//                            ->like('users.id',$keyword)
//                            ->orLike('users_info.nickname',$keyword)
//                            ->orLike('users_info.location',$keyword)
//                            ->orderBy('users.create_at','desc')
//                            ->paginate($limit),
//                        'pager' => $this->admin_m->pager
//                    ];
//                }
//            }
//        }else{
//            $data = [
//                'users' => $this->admin_m->select('users.id,users.phone,users.idx,users.login_ip,users.type,
//                    users_info.nickname,users.gender,users_info.age,users_info.location,users_info.location2')
//                    ->join('users_info','users.id = users_info.uid','left')
//                    ->orderBy('users.create_at','desc')
//                    ->paginate($limit),
//                'pager' => $this->admin_m->pager
//            ];
//        }

        $data['total'] = $this->admin_m->total_users();
        $data['limit'] = $limit;
        $data['getPage'] = $getpage;
        $data['keyword'] = $keyword;

        $result['lastPage'] = (string)$data['pager']->getLastPage();
        $result['getPreviousPageURI'] = (string)$data['pager']->getPreviousPageURI();
        $result['getNextPageURI'] = (string)$data['pager']->getNextPageURI();
        $result['getPageCount'] = (string)$data['pager']->getPageCount();
        $result['getPageCount'] = (string)$data['pager']->getPageCount();

        echo view('admin/admin_userlist_v', $data);
    }

    public function getUser()
    {
        $id = $this->request->getGetPost('id');
        $user_info = $this->oauth_m->user_info($id);

        if ($user_info->location !== '' && $user_info->location !== null) {
            $arr = region[$user_info->location];
            $user_info->locationArr = $arr;
        }

        echo json_encode($user_info);
    }

    public function userEdit()
    {
        $pk = $this->request->getGetPost('pk');
        $key = $this->request->getGetPost('name');
        $value = $this->request->getGetPost('value');

        $result = $this->admin_m->edit_user($pk, $key, $value);

        if ($key == 'users_info.location') {
            $arr = region[$value];

            echo json_encode($arr);
        }
    }

    public function users_info_update()
    {
        $arrayRequest = $this->request->getGetPost();

        $pk = $arrayRequest['pk'];

        $age = $arrayRequest['age'];

        if ($age !== '-1') {
            if ($age < 16 || $age > 60) {
                echo 'ageFail';
                exit;
            }
        }

        unset($arrayRequest['pk']);

        $data = $this->admin_m->update_user($pk, $arrayRequest);

        echo json_encode($data);
    }

    public function imgUpload()
    {
        $id = $this->request->getPost('id');
        $img_division = $this->request->getPost('img_division');

        if ($img_division == 2) {
            $img_count = $this->files_m->getFileCount($id, $img_division);
            if ($img_count >= 5) {
                echo  'false';
                exit;
            }
        }
        $user_info = $this->oauth_m->user_info($id);

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

                if ($img->getSize() >= 15242880) {
                    $respond = $this->res(400, '업로드용량 초과파일 포함.');

                    return $this->response->setJSON($respond);
                }
            }

            $year = date('Y', time());
            $month = date('m', time());

            $img_list = $this->files_m->getFileDivision($id, $img_division);
            if ($img_division == 1) {
                foreach ($img_list as $gi) {
                    if (file_exists(profileFolder . $gi->file_url_real . $gi->f_tempname)) {
                        unlink(profileFolder . $gi->file_url_real . $gi->f_tempname);
                        unlink(profileFolder . $gi->file_url_thumb . $gi->f_tempname);
                    }
                }
                $this->oauth_m->img_delete($id, $img_division);
            }

            foreach ($imagefile['img'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getRandomName();

                    if ($img_division == 1) {
                        $folder = 'profile';
                    } elseif ($img_division == 2) {
                        $folder = 'timeline';
                    } else {
                        $folder = 'gallery';
                    }

                    if (!is_dir(UPLOADPATH.$folder.'/'.$year.'/'.$month.'/')) {
                        mkdir(UPLOADPATH.$folder.'/'.$year.'/'.$month.'/', 0777, true);
                        mkdir(UPLOADPATH.$folder.'/'.$year.'/'.$month.'/thumb/', 0777, true);
                    }
                    $img->move(UPLOADPATH.$folder.'/'.$year.'/'.$month.'/', $newName);

                    try {
                        $image = \Config\Services::image()
                            ->withFile(UPLOADPATH.$folder.'/'.$year.'/'.$month.'/'.$newName)
                            ->fit(320, 320, 'center')//crop
//                            ->resize(320, 320, true, 'height')
                            ->save(UPLOADPATH.$folder.'/'.$year.'/'.$month.'/thumb/'.$newName, 70);
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
                        'file_url_real' => $year.'/'.$month.'/',
                        'file_url_thumb' => $year.'/'.$month.'/thumb/',
                        'ext' => $ext,
                        'create_at' => date('Y-m-d H:i:s'),
                        'division' => $img_division,
                    );

                    $last_id = $this->oauth_m->img_upload($data);
                }
            }
            //return
            $images = $this->files_m->getFileDivision($id, $img_division);
            if ($img_division == 1) {
                $folder = profileFolder;
            } elseif ($img_division == 2) {
                $folder = timelineFolder;
            } else {
                $folder = galleryFolder;
            }
            $data = array(
                'id' => $id
            );
            $arr = array();
            foreach ($images as $img) {
                $file = array(
                    'f_idx' => $img->f_idx,
                    'real' => $folder.$img->file_url_real.$img->f_tempname,
                    'thumb' => $folder.$img->file_url_thumb.$img->f_tempname
                );
                array_push($arr, $file);
            }
            $data['files'] = $arr;

            header('Content-Type: application/json');
            echo json_encode($data);
        }
    }


    public function insert_block()
    {
        $id = $this->request->getPost('id');
        $min = $this->request->getPost('min');
        $hour = $this->request->getPost('hour');
        $day = $this->request->getPost('day');

        $time = new Time('now');

        if ($min == "" && $hour == "" && $day == "") {
            echo '제한시간 입력오류';
            exit;
        }

        if ($min) {
            $blocktime = $time->addMinutes($min);
        }
        if ($hour) {
            $blocktime = $time->addHours($hour);
        }
        if ($day) {
            $blocktime = $time->addDays($day);
        }

        $stringNow = $time->toDateTimeString();
        $stringTime = $blocktime->toDateTimeString();

        //차단 datetime
        $year = date('Y', strtotime($stringTime));
        $month = date('m', strtotime($stringTime));
        $day = date('d', strtotime($stringTime));
        $hour = date('h', strtotime($stringTime));
        $min = date('i', strtotime($stringTime));
        $second = date('s', strtotime($stringTime));

        $data = array(
                    'id' => $id,
                    'y' => $year,
                    'ym' => $year.$month,
                    'ymd' => $year.$month.$day,
                    'h' => $hour,
                    'his' => $hour.$min.$second,
                    'admin_id' => $this->session->get('id'),
                    'create_at' => $stringNow,
                    'status' => 1
        );

        $this->admin_m->insert_block($data);


        var_dump($year.'-'.$month.'-'.$day.'-'.$hour.'-'.$min.'-'.$second);

        $time2 = Time::create($year, $month, $day, $hour, $min, $second);
        var_dump($time2);
        var_dump($time);
        exit;

        $result = $time->isAfter($blocktime);  // 블럭 시간이 현재시간 이후인지 체크

        echo $blocktime;
    }

    public function block_user_chk()
    {
        $id = $this->request->getPost('id');
        $time = new Time('now');

        $data = $this->admin_m->get_block($id);
        $ymdhis = date_parse($data->ymd.$data->his);
        $year = $ymdhis['year'];
        $month = $ymdhis['month'];
        $day = $ymdhis['day'];
        $hour = $ymdhis['hour'];
        $min = $ymdhis['minute'];
        $second = $ymdhis['second'];

        $blocktime = Time::create($year, $month, $day, $hour, $min, $second);
        $result = $time->isAfter($blocktime);

        var_dump($result);
    }

    public function delete_user()
    {
        $idx = $this->request->getGetPost('idx');

        $arr = array();
        for ($i = 0; $i<count($idx); $i++) {
            $user_info = $this->oauth_m->idx_user_info($idx[$i]);
            $result = $this->admin_m->delete_user($idx, $user_info->id);
            if ($result == 'success') {
                $this->socket_io(16000, array('message'=>'시스템에 의해 로그아웃 및 현재 접속을 종료합니다', 'nickname' =>$user_info->nickname));
            }
            array_push($arr, $user_info->nickname);
        }
        echo json_encode($result);
    }

    public function delete_profile()
    {
        $id = $this->request->getGetPost('id');
        $delUrl = $this->request->getGetPost('delUrl');

        $result = $this->admin_m->delete_profile($id);

        if ($result) {
            $real = str_replace('/thumb', '', $delUrl);
            if (file_exists('/home/ntalk/files'.$delUrl)) {
                unlink('/home/ntalk/files'.$delUrl);
                unlink('/home/ntalk/files'.$real);
                echo json_encode("success");
            } else {
                echo json_encode("fail");
            }
        }
    }

    public function sms_verification()
    {
        $this->admin_m->sms_verification();
    }

    // ==============================================================
    // 타임라인
    // ==============================================================
    public function timeline_lists()
    {
        $getpage = $this->request->getGetPost('page');
        if ($getpage == "") {
            $getpage = 1;
        }

        $keyword = $this->request->getGetPost('keyword');

        //리스트 검색 회원 접근시
        $whereArr[] = "";
        $limit = 10;

        if ($this->request->getGet('keyword')) {
            $division = $this->request->getGet('division');
            $keyword = $this->request->getGet('keyword');
            if ($keyword !== "" && $keyword !== null) {
                if ($division !== "" && $division !== null) {
                    $whereArr[$division] = $keyword;
                    $data = [
                        'timeline' => $this->timeline_m->select('t_idx,fgender,title,time_line.uid,time_line.flocation,time_line.flocation2,time_line.minAge,time_line.maxAge,
                                            users_info.nickname,comment,time_line.create_at,users.token,
                                            users.phone,users.gender,users_info.age,users.hash,users_info.location,users_info.location2')
                            ->join('users', 'users.id = time_line.uid', 'left')
                            ->join('users_info', 'time_line.uid = users_info.uid', 'left')
                            ->like($whereArr)
                            ->orderBy('time_line.create_at', 'desc')
                            ->paginate($limit),
                        'pager' => $this->timeline_m->pager
                    ];
                } else {
                    $data = [
                        'timeline' => $this->timeline_m->select('t_idx,fgender,title,time_line.uid,time_line.flocation,time_line.flocation2,time_line.minAge,time_line.maxAge,
                                            users_info.nickname,comment,time_line.create_at,users.token,
                                            users.phone,users.gender,users_info.age,users.hash,users_info.location,users_info.location2')
                            ->join('users', 'users.id = time_line.uid', 'left')
                            ->join('users_info', 'time_line.uid = users_info.uid', 'left')
                            ->like('users.id', $keyword)
                            ->orLike('users_info.nickname', $keyword)
                            ->orLike('users_info.location', $keyword)
                            ->orderBy('time_line.create_at', 'desc')
                            ->paginate($limit),
                        'pager' => $this->timeline_m->pager
                    ];
                }
            }
        } else {
            $data = [
                'timeline' => $this->timeline_m->select('t_idx,fgender,title,time_line.uid,time_line.flocation,time_line.flocation2,time_line.minAge,time_line.maxAge,
                                            users_info.nickname,comment,time_line.create_at,users.token,
                                            users.phone,users.gender,users_info.age,users.hash,users_info.location,users_info.location2')
                    ->join('users', 'users.id = time_line.uid', 'left')
                    ->join('users_info', 'time_line.uid = users_info.uid', 'left')
                    ->orderBy('time_line.create_at', 'desc')
                    ->paginate($limit),
                'pager' => $this->timeline_m->pager
            ];
        }

        $data['total'] = $this->timeline_m->total_timeline();
        $data['limit'] = $limit;
        $data['getPage'] = $getpage;

        $result['lastPage'] = (string)$data['pager']->getLastPage();
        $result['getPreviousPageURI'] = (string)$data['pager']->getPreviousPageURI();
        $result['getNextPageURI'] = (string)$data['pager']->getNextPageURI();
        $result['getPageCount'] = (string)$data['pager']->getPageCount();
        $result['getPageCount'] = (string)$data['pager']->getPageCount();
        echo view('admin/admin_timelinelist_v', $data);
    }

    public function getTimeLineWithUser()
    {
        $t_idx = $this->request->getGetPost('id');

        $result = $this->admin_m->get_timeline($t_idx);

        $result->profile = $this->admin_m->get_profile_img($result->id);

        $result->files = $this->admin_m->get_timeline_img($result->id);

        if ($result->flocation !== '' && $result->flocation !== null) {
            $arr = region[$result->flocation];
            $result->locationArr = $arr;
        }

        echo json_encode($result);
    }

    public function timelineEdit()
    {
        $pk = $this->request->getGetPost('pk');
        $key = $this->request->getGetPost('name');
        $value = $this->request->getGetPost('value');

        if ($key == "minAge" || $key == "maxAge") {
            if ($value == "" || $value == "0") {
                $value = "-1";
            }
//            $result = $this->admin_m->preference_age($pk, $key, $value);
//            echo json_encode($result);
//            exit;
        }

        if ($key == 'users_info.location') {
            $arr = region[$value];
            echo json_encode($arr);
            exit;
        }

        $result = $this->admin_m->edit_timeline($pk, $key, $value);
    }

    public function time_line_update()
    {
        $arrayRequest = $this->request->getGetPost();

        $pk = $arrayRequest['pk'];

        unset($arrayRequest['pk']);

        $data = $this->admin_m->update_timeline($pk, $arrayRequest);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function timeline_imgUpload()
    {
        $denyfile = array('php', 'php3', 'exe', 'cgi', 'phtml', 'html', 'htm', 'pl', 'asp', 'jsp', 'inc', 'dll', 'js', 'zip');
        if ($imagefile = $this->request->getFiles('files[]')) {
            $arr = array();
            foreach ($imagefile['files'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getRandomName();
                } else {
                    $respond = $this->res(400, '잘못된 요청입니다.');
                    return $this->response->setJSON($respond);
                }
                array_push($arr, $newName);
            }
        }

        var_dump($arr);
    }

    public function delete_timeline()
    {
        $idx = $this->request->getGetPost('idx');
        $result = $this->admin_m->delete_timeline($idx);

        echo json_encode($result);
    }
    public function delete_timeline_img()
    {
        $id = $this->request->getGetPost('id');
        $f_idx = $this->request->getGetPost('f_idx');


        $result = $this->admin_m->delete_timeline_img($id, $f_idx);

        $del_img = $this->files_m->getFileIdx($id, $f_idx);


        if (file_exists('/home/ntalk/files/timeline/'.$del_img->file_url_real.$del_img->f_tempname)) {
            var_dump($del_img);
            unlink('/home/ntalk/files/timeline/'.$del_img->file_url_real.$del_img->f_tempname);
            unlink('/home/ntalk/files/timeline/'.$del_img->file_url_thumb.$del_img->f_tempname);
            echo json_encode("success");
        } else {
            echo json_encode("fail");
        }
    }

    public function timeline_declaration()
    {
    }

    // ==============================================================
    // NODE Socket
    // ==============================================================
    public function socket_io($cmd, $data)
    {
        $client = new Client(new Version2X('https://ntalk.me:2580', ['context' => ['ssl' => ['verify_peer_name' => false, 'verify_peer' => false]]]));
        $client->initialize();

        try {
            $client->emit(
                'SUBMIT',
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
}
