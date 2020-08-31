<?php namespace App\Models\Admin;

use App\Controllers\Api\Users;
use App\Models\Api\Timeline_m;
use App\Models\Api\Files_m;
use App\Models\Api\Oauth_m;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;

//use mysql_xdevapi\Exception;

class Admin_m extends Model
{
    protected $table      = 'users';
    protected $returnType = 'object';
    protected $tempReturnType = 'object';
    protected $primaryKey = 'idx';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

    public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);
        $this->timeline_m = new Timeline_m();
        $this->files_m = new Files_m();
    }

    // ==============================================================
    // 회원 모델
    // ==============================================================
    public function total_users()
    {
        $builder = $this->db->table($this->table);
        $result = $builder->countAllResults();
        return $result;
    }


    public function admin_login($id)
    {
        $builder = $this->db->table('admin_user');
        $builder->where('id', $id);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }


    public function edit_user($pk,$key,$value)
    {
        try {
            $sql = "update users join users_info on users.id = users_info.uid set ".$key." = ? where users.idx = ?";
            $result = $this->db->query($sql, [$value, $pk]);
            return $result->resultID;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function delete_user($idx,$id)
    {
        try{
            $query = "delete u1, u2, u3, u4, u5, u6, u7 from users as u1
                        left join users_info as u2 on u1.id = u2.uid
                        left join phone as u3 on u1.id = u3.id
                        left join time_line as u4 on u1.id = u4.uid
                        left join users_favorite as u5 on u1.id = u5.id
                        left join device as u6 on u1.idx = u6.u_idx
                        left join files as u7 on u1.id = u7.u_id
                        where u1.id = ?";
            $result = $this->db->query($query,$id);

            $files = $this->files_m->getFile($id);

            foreach ($files as $fl){
                switch ($fl->division){
                    case '0' :
                        $folder = '/home/ntalk/files/gallery/';
                        break;
                    case '1' :
                        $folder = '/home/ntalk/files/profile/';
                        break;
                    default :
                        $folder = '/home/ntalk/files/timeline/';
                        break;
                }
                unlink($folder.$fl->file_url_real.$fl->f_tempname);
                unlink($folder.$fl->file_url_thumb.$fl->f_tempname);
            }
            return 'success';
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function update_user($pk,$data)
    {
        try {
            $sql = "update users join users_info on users.id = users_info.uid set ";

            $last_index = count($data)-1;

            if(array_key_exists("location2", $data)){
                $i = 0;
                foreach ($data as $key => $value){

                    if($i == $last_index){
                        $sql .= $key . " = '" . $value . "' ";
                    }else {
                        $sql .= $key . " = '" . $value . "', ";
                    }

                    $i++;
                }
                $sql .= "where users.idx = ".$pk;
                $result = $this->db->query($sql);
                return $result->resultID;
            }else{
                $i = 0;
                foreach ($data as $key => $value){

                    if($i == $last_index){
                        $sql .= $key . " = '" . $value . "' ";
                    }else {
                        $sql .= "location2 = '', ";
                        $sql .= $key . " = '" . $value . "', ";
                    }

                    $i++;
                }
                $sql .= "where users.idx = ".$pk;
                $result = $this->db->query($sql);
                return $result->resultID;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function delete_profile($id)
    {
        $model = new Files_m();
        try {
            $builder = $this->db->table('files');
            $builder->where('u_id',$id);
            $builder->where('division',1);
            $result = $builder->delete();
            return $result;
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function get_profile_img($id)
    {
        $builder = $this->db->table('files');
        $builder->where('division', 1);
        $builder->where('u_id',$id);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function sms_verification()
    {
        $builder = $this->db->table('sms_verification');
        $builder->truncate();
    }

    public function insert_block($data)
    {
        try {
            $builder = $this->db->table('users_block');
            $result = $builder->insert($data);
            return $result->resultID;
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    public function get_block($id)
    {
        $builder = $this->db->table('users_block');
        $builder->where('id',$id);
        return $builder->get()->getRow();
    }

    // ==============================================================
    // 타임라인 모델
    // ==============================================================
    public function delete_timeline($idx)
    {
        $builder = $this->db->table('time_line');
        $builder->whereIn('t_idx',$idx);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function edit_timeline($pk,$key,$value)
    {
        try {
            $builder = $this->db->table('time_line');
            $builder->set($key,$value);
            $builder->where('t_idx',$pk);
            $builder->update();

            return $key;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_timeline($t_idx)
    {
        $builder = $this->db->table('time_line');
        $builder->join('users_info','users_info.uid = time_line.uid');
        $builder->join('users','users.id = time_line.uid');
        $builder->where('t_idx',$t_idx);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function get_timeline_img($id)
    {
        $builder = $this->db->table('files');
        $builder->select('f_idx, file_url_thumb,f_tempname');
        $builder->where('division',2);
        $builder->where('u_id',$id);
        $builder->orderBy('f_idx','desc');

        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }
    public function preference_age($pk)
    {
        return $this->timeline_m->find($pk);
    }
    public function update_timeline($pk,$data)
    {
        if(array_key_exists("age", $data)){
            $data['minAge'] = $data['age'][0];
            $data['maxAge'] = $data['age'][1];
            unset($data['age']);
        }

        try {
            if(array_key_exists("flocation2", $data)){
                $builder = $this->db->table('time_line');
                $builder->set($data);
                $builder->where('t_idx',$pk);
                $builder->update();
                return true;
            }else{
                $builder = $this->db->table('time_line');
                $builder->set('flocation2','');
                $builder->set($data);
                $builder->where('t_idx',$pk);
                $builder->update();
                return true;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function delete_timeline_img($id,$f_idx)
    {
        try {
            $builder = $this->db->table('files');
            $builder->where('u_id',$id);
            $builder->where('f_idx',$f_idx);
            $result = $builder->delete();
            return $result;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}