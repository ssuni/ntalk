<?php namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Oauth as oauthmodel;

class Users extends ResourceController
{
    use ResponseTrait;

    public function index()
    {
        return $this->respond([['user'],[2]]);
    }

    public function show($id=null)
    {
        return $this->response->setJSON(['userget'=>$id]);
    }

    public function create()
    {
        $model = new oauthmodel();

        if ($model->errors())
        {
            $this->fail($model->errors());
        }
        $data = $this->request->getPost();

        $result = $model->insert($data);
        return $this->respond(['create']);
        return $this->respondCreated($result);
    }

    public function update($id=null)
    {

        return $this->respond(['update']);
        return $this->respond(['up'=>$id]);
    }

    public function delete($id=null)
    {
        return $this->respond(['delete']);
        return $this->respondDeleted($id);
    }

    public function findid($id=null)
    {
        return $this->respond(['findid'=>$id]);
        return $this->respond([['user'],[$id]]);
    }
    //--------------------------------------------------------------------

}
