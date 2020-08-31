<?php namespace App\Controllers;

class Pop extends BaseController
{

    public function _remap($method)
    {
        echo view('inc/header_v');
        echo view('pop_main_v');
        if($method)
        {
            $this->$method();
        }
        echo view('inc/footer_v');
    }

    public function main()
    {
        echo view('pop_main_v');
    }

    public function bookmark()
    {
        return view('pop_bookmark_v');
    }

    public function photo()
    {
        echo view('pop_img_v');
    }

    public function timeline()
    {
        echo view('pop_timeline_v');
    }

    public function chat()
    {
        echo view('chat_v');
    }

    //--------------------------------------------------------------------

}
