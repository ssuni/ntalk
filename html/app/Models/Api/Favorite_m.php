<?php namespace App\Models\Api;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class Favorite_m extends Model
{
    protected $table = 'users_favorite';
    protected $returnType = 'array';
    protected $tempReturnType = 'array';
    protected $primaryKey = 'idx';
    protected $allowedFields = ['id', 'favorite_id','create_at'];


    public function getFavorite($id)
    {
        return $this->where('id',$id)->findAll();
    }

    public function setFavorite($id,$favorite_id)
    {
        $data = ['id' => $id , 'favorite_id'=>$favorite_id, 'create_at' => date('Y-m-d H:i:s') ];
        $this->insert($data);
        return $last_id = $this->db->insertID();
    }

    public function deleteFavorite($id,$favorite_id)
    {
        try {
            $result = $this->where(['id' => $id , 'favorite_id' => $favorite_id ])->delete();
            return $result->resultID;
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    public function duplicateInspection($id,$favorite_id)
    {
        return $this->where(array('id' => $id , 'favorite_id' => $favorite_id))->countAllResults();
    }

    public function getFavoriteUserList($id)
    {
        return $this->select('favorite_id')->where('id',$id)->findAll();
    }

    public function favorite_list($arg)
    {
        return $this->select('favorite_id')->whereIn('favorite_id',$arg)->findAll();
    }

    public function favorite_userinfo($id)
    {
        $builder = $this->db->table('users');
        $builder->select('users.idx,users.id,users.phone,users.gender,
                        users_info.age,users_info.nickname,
                        users_info.location,users_info.location2,
                        concat("https://files.ntalk.me/profile/",file_url_thumb,f_tempname) as profile_thum,
                        concat("https://files.ntalk.me/profile/",file_url_real,f_tempname) as profile_real,
                        ');
        $builder->join('users_info', 'users.id = users_info.uid', 'left');
        $builder->join('files', 'users.id = files.u_id and division = 1', 'left');
        $builder->Where('id', $id);
        $builder->orderBy('files.create_at', 'desc');
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }
}