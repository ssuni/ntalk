<?php namespace App\Models\Api;

use CodeIgniter\Model;

class Fcm_m extends Model
{
    protected $table = 'device';
    protected $returnType = 'array';
    protected $tempReturnType = 'array';
    protected $primaryKey = 'd_idx';

    public function getToken()
    {
        $builder = $this->db->table('device');
        $builder->select('fcm_token');
        $builder->where('os','android');
        return $builder->get()->getResultArray();
    }
}