<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Api\Oauth_m;

class Main extends BaseController
{

    public function __construct()
    {
        $this->model = new Oauth_m();
        $this->session = \Config\Services::session();
        helper('alert');
//       if(!isset(json_decode($this->session->get('user_details'))->id)){
//            alert('로그인 후 이용 가능합니다.','/auth');
//       }
    }

    public function _remap($method)
    {
        echo view('inc/header_v');

        if($method == 'index') {
            echo view('inc/menu_v');
            echo view('inc/top_v');
            echo view('inc/aside_v');
        }
        if($method)
        {
            $this->$method();
        }
        if($method == 'index') {
            echo view('inc/bside_v');
        }
        echo view('inc/footer_v');
    }

    public function index()
    {
//        echo $this->session->get('id');

        $data['list'] = $this->model->userlist();

        echo view('timeline_v',$data);
    }

    public function chat()
    {
        echo view('chat_v');
    }

    public function del_user()
    {
        $idx = $this->request->getGetPost('idx');
        $id = $this->request->getGetPost('id');

        $data = $this->model->del_user($id);

        alert('유저삭제','/main');
    }

}

