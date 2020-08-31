<?php namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Api\Timeline_m;
use App\Models\Api\Files_m;
use App\Models\Api\Oauth_m;
use CodeIgniter\I18n\Time;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;

class Photo extends BaseController
{
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->oauth_m = new Oauth_m();
        $this->file_m = new Files_m();
    }

    public function lists()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $getpage = $this->request->getGetPost('page');
        $division = $this->request->getHeaderLine('type');

        $page = $this->request->getGetPost('page');
        if($getpage == ""){
            $getpage = 1;
        }else{
            $getpage = $this->request->getGetPost('page');
        }

        $limit = 60;
        $whereArr[] = "";

        $db = new Oauth_m();

        $builder = $db->table('users');

        $builder->select('users.id,users_info.nickname,
                        files.f_tempname,files.file_url_real,files.file_url_thumb');
        $builder->join('users_info','users.id = users_info.uid','left');
        $builder->join('files', 'users.id = files.u_id and (division = 0 or division = 2)','left');
                $builder->whereIn('division',[0,2]);
        $builder->groupBy('users.id');

        $builder->orderBy('files.create_at','desc');

        $list = $builder->paginate($limit);

//        $this->oauth_m->select('users.id,users_info.nickname,users.gender,users_info.age,
//                               users_info.location,users_info.location2,users.token,users.hash,
//                               files.create_at,files.division,
//                               files.f_idx,files.file_url_real,files.file_url_thumb,files.f_tempname
//                            ');
//        $this->oauth_m->join('users_info','users_info.uid = users.id','left');
//        $this->oauth_m->join('files','users.id = files.u_id','left');
////        $this->oauth_m->where($whereArr);
//        $this->oauth_m->whereIn('files.division',[0,2]);
//        $this->oauth_m->orderBy('files.create_at','desc');
//        $this->oauth_m->distinct();
//        $list =  $this->oauth_m->distinct()->paginate($limit);
//        $list =  $this->oauth_m->getCompiledSelect($limit);

        $data = [
            'users' => $list,
            'pager' => $builder->pager
        ];

        $return = array();

        $files = $this->file_m->photoList();
        $arr = array();
        foreach ($files as $fl){
                if ($fl->division == 2) {
                    $arrfile = array(
                        'id' => $fl->u_id,
                        'nickname' => $fl->nickname,
                        'division' => $fl->division,
                        'f_idx' => $fl->f_idx,
                        'real' => timelineFolder . $fl->file_url_real . $fl->f_tempname,
                        'thumb' => timelineFolder . $fl->file_url_thumb . $fl->f_tempname
                    );
                    array_push($arr, $arrfile);
                }
                if ($fl->division == 0) {
                    $arrfile = array(
                        'id' => $fl->u_id,
                        'nickname' => $fl->nickname,
                        'division' => $fl->division,
                        'f_idx' => $fl->f_idx,
                        'real' => galleryFolder . $fl->file_url_real . $fl->f_tempname,
                        'thumb' => galleryFolder . $fl->file_url_thumb . $fl->f_tempname
                    );
                    array_push($arr, $arrfile);
                }
        }

        $result['files'] = $arr;
//        for ($i=0; $i< count($data['users']); $i++){
//
//
//            $files = $this->file_m->getFile($data['users'][$i]->id);
//
////                $return[$i] = array(
////                    'id' => $data['users'][$i]->id,
////                    'nickname' => $data['users'][$i]->nickname,
////                    'create_at' => $data['users'][$i]->create_at,
////                    'token' => $data['users'][$i]->token,
////                    'hash' => $data['users'][$i]->hash,
////                    'age' => $data['users'][$i]->age,
////                    'gender' => $data['users'][$i]->gender,
////                    'location' => $data['users'][$i]->location,
////                    'location2' => $data['users'][$i]->location2
////                );
////                if ($data['users'][$i]->f_tempname) {
////                    $return[$i]['real'] = profileFolder . $data['users'][$i]->file_url_real . $data['users'][$i]->f_tempname;
////                    $return[$i]['thumb'] = profileFolder . $data['users'][$i]->file_url_thumb . $data['users'][$i]->f_tempname;
////                }
//
//                $arr = array();
//                foreach ($files as $fl) {
//                    if ($fl->division == 2) {
//                        $arrfile = array(
//                            'division' => $fl->division,
//                            'f_idx' => $fl->f_idx,
//                            'real' => timelineFolder . $fl->file_url_real . $fl->f_tempname,
//                            'thumb' => timelineFolder . $fl->file_url_thumb . $fl->f_tempname
//                        );
//                        array_push($arr, $arrfile);
//                    }
//                    if ($fl->division == 0) {
//                        $arrfile = array(
//                            'division' => $fl->division,
//                            'f_idx' => $fl->f_idx,
//                            'real' => galleryFolder . $fl->file_url_real . $fl->f_tempname,
//                            'thumb' => galleryFolder . $fl->file_url_thumb . $fl->f_tempname
//                        );
//                        array_push($arr, $arrfile);
//                    }
//                }
//                $return[$i]['files'] = $arr;
//
////            if($arr == "" || $arr == null){
//////                unset($return[$i]);
////            }            if($arr == "" || $arr == null){
//////                unset($return[$i]);
////            }
//
//        }

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

    public function view()
    {
        $token = $this->request->getHeaderLine('authtoken');
        $division = $this->request->getHeaderLine('type');

        $nickname = $this->request->getGetPost('nickname');

        $get_id = $this->oauth_m->get_id(array('nickname'=>$nickname));

        $user_info = $this->oauth_m->getUserInfo($nickname);
        $files = $this->file_m->getFileDesc($get_id);

        $result = array();
        $arr = array();
        foreach ($files as $fl)
        {
            switch ($fl->division)
            {
                case 1 :
                    $folder = profileFolder;
                    break;
                case 2 :
                    $folder = timelineFolder;
                    break;
                default :
                    $folder = galleryFolder;
                    break;
            }
            if($fl->division == 0) {
                $file = array(
                    'f_idx' => $fl->f_idx,
                    'division' => $fl->division,
                    'real' => $folder . $fl->file_url_real . $fl->f_tempname,
                    'thumb' => $folder . $fl->file_url_thumb . $fl->f_tempname
                );
                array_push($arr, $file);
            }
        }
        $result['files'] = $arr;

        $respond = $this->res(200, '조회성공',$result);
        return $this->response->setJSON($respond);

    }

}