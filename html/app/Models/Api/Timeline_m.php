<?php namespace App\Models\Api;

use CodeIgniter\Model;
use mysql_xdevapi\Exception;
use CodeIgniter\Database\BaseBuilder;
use App\Models\Api\Files_m;

class Timeline_m extends Model
{
    protected $table      = 'time_line';
    protected $returnType = 'object';
    protected $tempReturnType = 'object';
    protected $primaryKey = 't_idx';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->db2 = \Config\Database::connect('mongo');
        $this->files_m = new Files_m();
        $client = new \MongoDB\Client("mongodb://127.0.0.1:27017");
        $this->collection = $client->database->collection;
    }

    public function feeds()
    {
        $builder = $this->db->table('users_info');
        $builder->select('tag');
        $builder->where('tag_yn !=',1);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function index()
    {
        $builder = $this->db->table($this->table);
        $builder->orderBy('create_at','desc');
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function insert_timeline($data)
    {
        $builder = $this->db->table('time_line');
        $builder->insert($data);
        $result = $this->db->insertID();
        return $result;
    }

    public function prev($id,$last_id)
    {
        $builder = $this->db->table('time_line');
        $builder->select('t_idx');
        $builder->where('uid',$id);
        $builder->where('t_idx <',$last_id);
        $builder->limit(1);
        $builder->orderBy('t_idx','desc');
        $query = $builder->get();
        $result = $query->getRow();

        if($result) {
            return $result->t_idx;
        }else{
            return "";
        }
    }

    public function get_timeline($id)
    {
        $builder = $this->db->table('time_line');
        $builder->where('uid',$id);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function total_timeline()
    {
        $builder = $this->db->table('time_line');
        $result = $builder->countAllResults();
        return $result;
    }

    public function edit_timeline($t_idx,$data)
    {
        try{
            $builder = $this->db->table('time_line');
            $builder->set($data);
            $builder->where('t_idx',$t_idx);
            $builder->update();
            return 'success';
        }catch (Exception $exception){
            return $exception;
        }
    }

    public function delete_timeline($id,$t_idx)
    {
        try{
            $builder = $this->db->table('time_line');
            $builder->where('uid',$id);
            $builder->where('t_idx',$t_idx);
            $builder->delete();

            $builder = $this->db->table('files');
            $builder->where('u_id',$id);
            $builder->where('division',2);
            $builder->delete();
            return 'success';
        }catch (Exception $exception){
            return $exception;
        }
    }

    public function declaration_timeline($data)
    {
        $builder = $this->db->table('time_line_declaration');
        $builder->insert($data);
        $result = $this->db->insertID();
        return $result;
    }

    public function declaration_chk($t_idx,$idx)
    {
        $builder = $this->db->table('time_line_declaration');
        $builder->selectCount('t_idx');
        $builder->where('t_idx',$t_idx);
        $builder->where('reporter',$idx);
        $query = $builder->get();
        $result = $query->getRow();
        return $result->t_idx;
    }

    public function timeline_chk($id,$t_idx)
    {
        $builder = $this->db->table('time_line');
        $builder->selectCount('t_idx');
        $builder->where('uid',$id);
        $builder->where('t_idx',$t_idx);
        $query = $builder->get();
        $result = $query->getRow();
        return $result->t_idx;
    }

    public function timeline_tr($id)
    {
        $builder = $this->db->table('time_line');
        $builder->selectCount('t_idx');
        $builder->where('uid',$id);
        $query = $builder->get();
        $result = $query->getRow();
        return $result->t_idx;
    }

    public function timeline_presence($id)
    {
        $builder = $this->db->table('time_line');
        $builder->where('uid',$id);
        $builder->orderBy('create_at','desc');
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function timeline_set_session($id)
    {
        $builder = $this->db->table('time_line');
        $builder->select('t_idx,title,comment,minAge,maxAge,flocation,flocation2,fgender');
        $builder->where('uid',$id);
        $builder->orderBy('create_at','desc');
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function timeline_img_count($id)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id',$id);
        $builder->where('division',2);
        $result = $builder->countAllResults();

        return $result;
    }

    public function timeline_img_all($id)
    {
        $this->files_m->where('u_id',$id);
        $this->files_m->where('division',2);
        $count = $this->files_m->withDeleted()->countAllResults();

        return $count;
    }

    public function timeline_img_get($id,$f_idx)
    {
        $builder = $this->db->table('files');
        $builder->where('f_idx',$f_idx);
        $builder->where('u_id',$id);
        $builder->where('division',2);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function timeline_img_first($id)
    {
        return $this->files_m->where('u_id',$id)->where('deleted_at !=',null)->first();
    }

    public function timeline_img_softdelete($id,$f_idx)
    {
        try {
            $this->files_m->where('u_id',$id)->where('f_idx',$f_idx)->where('division',2)->delete();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function getDeleteImg($remove,$id)
    {
        $builder = $this->db->table('files');
        $builder->whereIn('f_idx',$remove);
        $builder->where('u_id',$id);
        $builder->where('division',2);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function allDeleteImg($id)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id',$id);
        $builder->where('division',2);
        $builder->orderBy('f_idx','asc');
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function timeline_img_delete($remove,$id)
    {
        try {
            $builder = $this->db->table('files');
            $builder->whereIn('f_idx',$remove);
            $builder->where('u_id',$id);
            $builder->where('division',2);
            $builder->delete();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }


}