<?php namespace App\Models\Api;

use CodeIgniter\Model;
use MongoDB;
use CodeIgniter\I18n\Time;

class Chat_m extends Model
{
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->db2 = \Config\Database::connect('mongo');
//        $this->session = \Config\Services::session();
        $client = new \MongoDB\Client("mongodb://127.0.0.1:27017");
        $this->collection = $client->database->collection;
    }

    public function get_declaration($reportId,$userId)
    {
        $builder = $this->db->table('room_declaration');
        $builder->select();
        $builder->where('document_id',$reportId);
        $builder->where('reporter',$userId);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function insert_declaration($data)
    {
        $builder = $this->db->table('room_declaration');
        $builder->insert($data);
        $result['last_id'] = $this->db->insertID();
        return $result;
    }

    public function delaration_list($arr_id)
    {
        $collection = (new MongoDB\Client)->ntalk->chat_reports;
        //  $mongoid = '5db011370212f60365aa5ad1';
        //  $cursor = $collection->find(["_id" => new MongoDB\BSON\ObjectID($mongoid)]);
        //  $cursor = $collection->find(["_id" => ['$in' => $ids]]);
        //  $array = iterator_to_array($cursor);
        $cursor = $collection->find();

        $data = array();
        foreach ( $cursor as  $value )
        {
            $arr = array(
                '_id' => $value['_id'],
                'report_userId' => $value['report_userId'],
                //mongodb time object 기본 형식으로 변환
                'report_at' => Time::createFromTimestamp($value['report_at']->__toString()/1000)->toDateTimeString(),
                'data' => $value
            );
            array_push($data,$arr);
        }
        return $data;
    }

    public function all_id()
    {
        $data = array();
        $builder = $this->db->table('room_declaration');
        $builder->select('document_id');
        $query = $builder->get();
        foreach ($query->getResultArray() as $row)
        {
            $data[] = $row['document_id'];
        }

        return $data;
    }
}