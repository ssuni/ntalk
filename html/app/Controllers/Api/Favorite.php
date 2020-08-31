<?php namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Api\Oauth_m;
use App\Models\Api\Favorite_m;
use CodeIgniter\I18n\Time;
use http\Env\Request;

class Favorite extends BaseController
{

    public function __construct()
    {
        $this->favorite_m = new Favorite_m();
        $this->oauth_m = new Oauth_m();
    }

    public function lists()
    {
        $user_info = $this->oauth_m->getUserInfo($this->request->getGetPost('nickname'));

        if(!$user_info){
            $respond = $this->res(404, '일치하는 회원정보가 없습니다.');
            return $this->response->setJSON($respond);
        }
        if($this->favoriteUserList($user_info->uid)){
            $list['favoriteUserList'] = $this->favoriteUserList($user_info->uid);
            $respond = $this->res(200, '즐겨찾기 리스트',$list);
        }else{
            $list['favoriteUserList'] = null;
            $respond = $this->res(200, '즐겨찾기 리스트',$list);
        }

        return $this->response->setJSON($respond);
    }

    public function insert()
    {

        $division = $this->request->getHeaderLine('type');
        $token = $this->request->getHeaderLine('authtoken');
        $favorite_user_nickname = $this->request->getGetPost('favorite_user');

        $user_info = $this->user_authentication($token, $division);

        if ($user_info == null) {
            $respond = $this->res(404, '사용자 정보가 없습니다.');
            return $this->response->setJSON($respond);
        }

        $favorite_user_info = $this->oauth_m->getUserInfo($favorite_user_nickname);

        //중복확인
        if (!$favorite_user_info) {
            $respond = $this->res(404, '사용자 정보가 없습니다.');
            return $this->response->setJSON($respond);
        }

        if($user_info->id == $favorite_user_info->uid){
            $respond = $this->res(406, '자신은 즐겨찾기 불가합니다.');
            return $this->response->setJSON($respond);
        }
        $chk = $this->favorite_m->duplicateInspection($user_info->id, $favorite_user_info->uid);

        if ($chk > 0) {
            $respond = $this->res(406, '이미 추가되었습니다.');
            return $this->response->setJSON($respond);
        } else {
            $last_id = $this->favorite_m->setFavorite($user_info->id, $favorite_user_info->uid);
            $data = $this->favorite_return_list($user_info->id);

            $respond = $this->res(200, '즐겨찾기 리스트.' , $data);
            return $this->response->setJSON($respond);
        }

    }

    public function delete()
    {
        $division = $this->request->getHeaderLine('type');
        $token = $this->request->getHeaderLine('authtoken');
        $favorite_user_nickname = $this->request->getGetPost('favorite_user');

        $user_info = $this->user_authentication($token, $division);

        if ($user_info == null) {
            $respond = $this->res(404, '사용자 정보가 없습니다.');
            return $this->response->setJSON($respond);
        }

        $favorite_user_info = $this->oauth_m->getUserInfo($favorite_user_nickname);

        if (!$favorite_user_info) {
            $respond = $this->res(404, '사용자 정보가 없습니다.');
            return $this->response->setJSON($respond);
        }
        $chk = $this->favorite_m->duplicateInspection($user_info->id, $favorite_user_info->uid);

        if ($chk > 0) {
            $last_id = $this->favorite_m->deleteFavorite($user_info->id, $favorite_user_info->uid);
            $data = $this->favorite_return_list($user_info->id);


            $respond = $this->res(200, '즐겨찾기 리스트.' , $data);
            return $this->response->setJSON($respond);
        } else {
            $respond = $this->res(404, '해당 닉네임 즐겨찾기 정보가 없습니다.');
            return $this->response->setJSON($respond);
        }
    }

    public function favorite_return_list($id)
    {
        $arr_favorite_id = $this->favorite_m->getFavorite($id);

        $arr_favorite = [];
        foreach ($arr_favorite_id as $lt){
            $favorite_id = $lt['favorite_id'];
            $arr_favorite[] = $favorite_id;
        }
        $list = $this->oauth_m->favorite_list($arr_favorite);

        $arr = array();
        for($i=0; $i<count($arr_favorite); $i++){
            $favorite_user_info = $this->favorite_m->favorite_userinfo($arr_favorite[$i]);
            array_push($arr , $favorite_user_info);
        }
        if(count($arr)>0) {
            $data['favoriteUserList'] = $arr;
        }else{
            $data['favoriteUserList'] = null;
        }
        return $data;
    }
}