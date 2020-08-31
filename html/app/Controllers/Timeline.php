<?php namespace App\Controllers;


class Timeline extends BaseController
{
    public function _remap($method)
    {
        echo view('inc/header_v');

        if($method == 'index' || $method == 'write') {
            echo view('inc/menu_v');
            echo view('inc/top_v');
            echo view('inc/aside_v');
        }
        if($method)
        {
            $this->$method();
        }
        if($method == 'index' || $method == 'write') {
            echo view('inc/bside_v');
        }
        echo view('inc/footer_v');
    }
    public function index()
    {
        echo view('timeline_v');
    }

    public function write()
    {
        echo view('timeline_write_v');
    }

    public function edit()
    {
        echo view('timeline_edit_v');
    }
}