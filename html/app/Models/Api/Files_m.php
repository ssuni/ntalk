<?php namespace App\Models\Api;

use CodeIgniter\Model;

class Files_m extends Model
{
    protected $table = 'files';
    protected $returnType = 'object';
    protected $tempReturnType = 'object';
    protected $primaryKey = 'f_idx';
    protected $useSoftDeletes = true;
    protected $useTimestamps = false;
    protected $deletedField = 'deleted_at';

    public function photoList()
    {
        $list = $this->join('users_info','users_info.uid = files.u_id','left')->
        whereIn('division',[0,2])->orderBy('create_at','desc')->findAll();
        return $list;
    }

    public function getFile($id)
    {
        $builder = $this->db->table('files');
        $builder->select('f_idx,u_id,file_url_real,file_url_thumb,f_tempname,division,create_at');
        $builder->where('u_id',$id);
        $builder->orderBy('f_idx','asc');
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }
    public function getFileDesc($id)
    {
        $builder = $this->db->table('files');
        $builder->select('f_idx,u_id,file_url_real,file_url_thumb,f_tempname,division,create_at');
        $builder->where('u_id',$id);
        $builder->orderBy('f_idx','desc');
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function getFileCount($id,$division)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id',$id);
        $builder->where('division',$division);
        $result = $builder->countAllResults();

        return $result;
    }

    public function getDeleteImgList($remove,$id,$img_division)
    {
        $builder = $this->db->table('files');
        $builder->whereIn('f_idx',$remove);
        $builder->where('u_id',$id);
        $builder->where('division',$img_division);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function getFileDivision($id,$division)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id',$id);
        $builder->where('division',$division);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }
    public function getFileDivisionDesc($id,$division)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id',$id);
        $builder->where('division',$division);
        $builder->orderBy('create_at','desc');
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function getTimelineFile($id)
    {
        $builder = $this->db->table('files');
        $builder->select('f_idx,u_id,file_url_real,file_url_thumb,f_tempname,division');
        $builder->where('u_id',$id);
        $builder->where('division',2);
        $builder->orderBy('f_idx','asc');
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function getFileIdx($id,$f_idx)
    {
        $builder = $this->db->table('files');
        $builder->select('file_url_real,file_url_thumb,f_tempname,division');
        $builder->where('f_idx',$f_idx);
        $builder->where('u_id',$id);
        $builder->where('division',2);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function getProfile($id,$f_idx)
    {
        return $this->where([ 'u_id' => $id , 'f_idx' => $f_idx , 'division' => 1 ])->first();
    }
}

