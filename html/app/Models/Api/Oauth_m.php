<?php namespace App\Models\Api;

use CodeIgniter\Model;
use mysql_xdevapi\Exception;

//use mysql_xdevapi\Exception;

class Oauth_m extends Model
{
    protected $table      = 'users';
    protected $returnType = 'object';
    protected $tempReturnType = 'object';
    protected $primaryKey = 'idx';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->db2 = \Config\Database::connect('mongo');
//        $this->session = \Config\Services::session();
        $client = new \MongoDB\Client("mongodb://127.0.0.1:27017");
        $this->collection = $client->database->collection;
    }

    public function get($id)
    {
        $builder = $this->db->table('users');
        $builder->join('sms_verification', 'users.phone = sms_verification.phone', 'left');
        $builder->Where('users.id', $id);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function get_id($arg)
    {
        $builder = $this->db->table($this->table);
        $builder->select('id');
        $builder->join('users_info','users.id = users_info.uid');
        $builder->where($arg);
        return $builder->get()->getRow()->id;
    }

    public function social_email($id)
    {
        $builder = $this->db->table('users');
        $builder->select('type');
        $builder->where('id',$id);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function userlist()
    {
        $builder = $this->db->table('users');
        $builder->where('level',1);
        $query = $builder->get();
        $result = $query->getResultArray();

        return $result;
    }

    public function del_user($id)
    {
        try {
            $builder = $this->db->table('users');
            $builder->where('id', $id);
            $builder->delete();

            $builder = $this->db->table('users_info');
            $builder->where('uidx', $id);
            $builder->delete();
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function get_login($id)
    {
        $builder = $this->db->table('users');
        $builder->where('users.id',$id);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function get_user($phone)
    {
        $builder = $this->db->table('users');
        $builder->where('phone',$phone);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function get_user_chk($phone,$email)
    {
        $builder = $this->db->table('users');
        $builder->where('phone',$phone);
        $builder->where('id',$email);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function phone_chk($phone)
    {
        $builder = $this->db->table('users');
        $builder->where('phone',$phone);
        return $builder->countAllResults();
    }

    public function user_info($id)
    {
        $builder = $this->db->table('users');
        $builder->select('users.idx,users.id,users.token,users.phone,users.gender,users.password,users.level,users.login_ip,
                        users.access_token,users.type,users.create_at,
                        users_info.age,users_info.nickname,
                        users_info.location,users_info.location2,users.hash,
                        file_url_real,file_url_thumb,f_tempname, concat(file_url_thumb,f_tempname) as profile,
                        users_info.tag');
        $builder->join('users_info', 'users.id = users_info.uid', 'left');
        $builder->join('files', 'users.id = files.u_id and division = 1', 'left');
        $builder->join('sms_verification', 'users.phone = sms_verification.phone', 'left');
        $builder->Where('id', $id);
        $builder->orderBy('files.create_at', 'desc');
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function modify($id,$arr)
    {
        try {
            $sql = "UPDATE users join users_info on users.id = users_info.uid
                    set users.gender = ? , users.phone = ? , users_info.age = ? , users_info.location = ?, users_info.location2 = ? , users_info.nickname = ?
                    WHERE id = ?";
            $this->db->query($sql, [$arr['gender'],$arr['phone'],$arr['age'],$arr['location'],$arr['location2'],$arr['nickname'],$id]);


        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function itemEditSubmit($data)
    {
        try {
            $sql = "UPDATE users left join users_info on users.id = users_info.uid
                    set users.gender = ? ,  users_info.age = ? , users_info.location = ?, users_info.location2 = ? , users_info.nickname = ?
                    WHERE id = ?";
            $this->db->query($sql, [$data['gender'],$data['age'],$data['location'],$data['location2'],$data['nickname'],$data['id']]);
//            $builder = $this->db->table('users');
//            $builder->join('users_info','users.id = users_info.uid','left');
//            $builder->set('users_info.nickname' , $data['nickanme']);
//            $builder->set('users_info.age' , $data['age']);
//            $builder->set('users_info.location' , $data['location']);
//            $builder->set('users_info.location2' , $data['location2']);
//            $builder->set('users.gender' , $data['gender']);
//            $builder->where('users.id',$data['id']);
//            $result = $builder->update();
//            return $result->resultID();
        } catch (\Exception $e) {
            echo $e->getMessage();

        }

    }

    public function itemEdit($id,$arg)
    {
        $key = array_keys($arg)[0];
        $value = array_values($arg)[0];


        if(count($arg) > 1) {
            $key2 = array_keys($arg)[1];
            $value2 = array_values($arg)[1];
        }
        if($key == 'gender'){
            $key = 'users.'.$key;
        }else{
            $key = 'users_info.'.$key;
        }
        try {
            if($key == 'users_info.location' && isset($key2) ) {
                $builder = $this->db->table('users_info');
                $builder->set('users_info.location',$value);
                $builder->set('users_info.location2',$value2);
                $builder->where('uid',$id);
                $builder->update();

                return array(explode('.',$key)[1] => $value , $key2 => $value2);
            }else if($key == 'users_info.location' && !isset($key2)){
                $builder = $this->db->table('users_info');
                $builder->set('users_info.location',$value);
                $builder->set('users_info.location2',"");
                $builder->where('uid',$id);
                $builder->update();

                return array(explode('.',$key)[1] => $value , 'location2'=> "");
            } else {
                $sql = "update users join users_info on users.id = users_info.uid set " . $key . " = ? where users.id = ?";
                $result = $this->db->query($sql, [$value, $id]);
                return array(explode('.',$key)[1] => $value);
            }
        }catch (Exception $e){
            $e->getMessage();
        }
    }

    public function phone_edit($id,$phone)
    {
        try {
            $builder = $this->db->table('users');
            $builder->set('phone',$phone);
            $builder->where('id',$id);
            $result = $builder->update();

            return 'true';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }

    public function getUserInfo($nickname)
    {
        $builder = $this->db->table('users_info');
        $builder->select('users_info.uid,users_info.uidx,users_info.nickname,users.type,users.phone,users_info.location,users_info.location2,users.gender,
                            users_info.age,
                    
                            (CASE WHEN users.updated_pass is null THEN users.create_at ELSE users.updated_pass END) as updated_pass,
                            t_idx,title,comment,minAge,maxAge,fgender,flocation,flocation2,
                            file_url_real,file_url_thumb,f_tempname,users.create_at as user_create_at
        ');
        $builder->join('time_line','users_info.uid = time_line.uid','left');
        $builder->join('users','users_info.uid = users.id','left');
        $builder->join('files','files.u_id = users_info.uid and division = 1','left');
        $builder->where('users_info.nickname',$nickname);

//       return $builder->getCompiledSelect();
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function getAnotherId($phone,$id)
    {
        $builder = $this->db->table('users');
        $builder->select('id,phone,gender,create_at,type,create_ip,login_ip,updated_pass');
        $builder->where('phone',$phone);
        $builder->whereNotIn('id',[$id]);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
        return $this->where('phone',$phone)->whereNotIn('id',$id)->getCompiledSelect();
    }

    public function get_count($id)
    {
        $builder = $this->db->table('users');
        $builder->selectCount('id');
        $builder->Where('id', $id);
        $result = $builder->countAllResults();
        return $result;
    }

    public function last_login($id)
    {
        try {
            $builder = $this->db->table('users');
            $builder->set('last_login', date("Y-m-d H:i:s"));
            $builder->where('id', $id);
            $builder->update();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function last_login_ip($id,$ip)
    {
        try {
            $builder = $this->db->table('users');
            $builder->set('login_ip', $ip);
            $builder->where('id', $id);
            $builder->update();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function token_chk($id, $token)
    {
        $builder = $this->db->table('users');
        $builder->selectCount('id');
        $builder->where('id', $id);
        $builder->where('token', $token);
        $query = $builder->get();
        $result = $query->getRow();

        return $result->id;
    }

    public function token_user_info($token)
    {
        $builder = $this->db->table('users');
        $builder->select('users.idx,users.id,users.gender,users.hash,
                        users_info.age,users_info.nickname,users_info.location,users_info.location2,users_info.tag,users_info.tag,
                        file_url_real,file_url_thumb,f_tempname,
                        ');
        $builder->join('users_info', 'users.id = users_info.uid', 'left');
        $builder->join('files', 'users.id = files.u_id and division = 1', 'left');
        $builder->where('users.token', $token);
//        $builder->where('files.sort', 1);

        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function idx_user_info($idx)
    {
        $builder = $this->db->table('users');
        $builder->select('users.idx,users.id,users.gender,users.hash,users.phone,
                        users_info.age,users_info.nickname,users_info.location,users_info.location2,users_info.tag,users_info.tag,
                         files.file_url_real,files.file_url_thumb,files.f_tempname
                        ');
        $builder->join('users_info', 'users.id = users_info.uid', 'left');
        $builder->join('files', 'users.id = files.u_id and division = 1', 'left');
        $builder->where('users.idx', $idx);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function profile_chk($id)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id',$id);
        $builder->where('division',1);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function token_reset($token, $id)
    {
        $builder = $this->db->table('users');
        $builder->set('token', $token);
        $builder->where('id', $id);
        $builder->update();
    }

    public function hash_chk($phone)
    {
        $builder = $this->db->table('sms_verification');
        $builder->select('certification');
        $builder->where('phone', $phone);
        $builder->where('status', 0);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function phone_limit_count($phone)
    {
        $builder = $this->db->table('phone');
        $builder->selectCount('id');
        $builder->where('phone', $phone);
        $query = $builder->get();
        $result = $query->getRow();
        return $result->id;
    }

    public function phone_limit($data)
    {
        $builder = $this->db->table('phone');
        $builder->insert($data);
        $result['last_id'] = $this->db->insertID();
        return $result;
    }

    public function insert_user($data)
    {
        $builder = $this->db->table('users');
        $builder->insert($data);
        $result = $this->db->insertID();
        return $result;
    }

    public function random_nick_insert($id,$idx,$nickname)
    {
        $builder = $this->db->table('users_info');
        $builder->set('uid',$id);
        $builder->set('uidx',$idx);
        $builder->set('nickname',$nickname);
        $builder->insert();

        $result = $this->db->insertID();
        return $result;
    }

    public function insert_userinfo($data)
    {
        $builder = $this->db->table('users_info');
        $builder->selectCount('uid');
        $builder->where('uid', $data['uid']);
        $query = $builder->get();
        $result = $query->getRow();

        if ($result->uid > 0) {
            try {
                $builder = $this->db->table('users_info');
                $builder->set('uidx', $data['uidx']);
                $builder->set('age', $data['age']);
                $builder->set('location', $data['location']);
                $builder->set('location2', $data['location2']);
                $builder->set('nickname', $data['nickname']);
                $builder->where('uid', $data['uid']);
                $builder->update();
                return 'success';
            } catch (Exception $exception) {
                return $exception;
            }
        } else {
            $builder->insert($data);
//            $result['last_id'] = $this->db->insertID();
            return $result;
        }
    }

    public function insert_userpreference($data)
    {
        $builder = $this->db->table('users_preference');
        $builder->insert($data);
        $result['last_id'] = $this->db->insertID();
        return $result;
    }

    public function insert_social($data)
    {
        $builder = $this->db->table('users');
        $builder->insert($data);
        $result['last_id'] = $this->db->insertID();
        return $result;
    }

    public function get_social($idx)
    {
        $builder = $this->db->table('users');
        $builder->where('idx', $idx);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function member_verification($email, $type)
    {
        $builder = $this->db->table('users');
        $builder->where('id', $email);
//        $builder->where('type', $type);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function get_token($idx)
    {
        $builder = $this->db->table('users');
        $builder->select('access_token,refresh_token');
        $builder->where('idx', $idx);
        $query = $builder->get();
        return $query->getRow();
    }

    public function delete_users($id)
    {
        try {
            $builder = $this->db->table('users');
            $builder->join('user_info','users.id = users_info.uid');
            $builder->join('phone','users.id = phone.id');
            $builder->where('id', $id);
            $builder->delete();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function sms_verification_update($phone)
    {
        try {
            $builder = $this->db->table('sms_verification');
            $builder->set('status', 1);
            $builder->where('phone', $phone);
            $builder->update();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function find_id($phone, $certification)
    {
        $builder = $this->db->table('users');
        $builder->select('users.id,users.type');
        $builder->join('sms_verification', 'users.phone = sms_verification.phone', 'left');
        $builder->where('sms_verification.phone', $phone);
        $builder->where('sms_verification.certification', $certification);
        $builder->whereIn('sms_verification.status', array(0));
        $query = $builder->get();
        $result = $query->getResult();

        $arr = array();
        foreach ($result as $r)
        {
            $obj = (object)array(
                'email' => $r->id,
                'type' => $r->type
                );
            array_push($arr ,$obj);
        }
        return $arr;
    }

    public function find_pass($id, $phone, $certification)
    {
        $builder = $this->db->table('users');
        $builder->selectCount('users.id');
        $builder->join('sms_verification', 'users.phone = sms_verification.phone', 'left');
        $builder->where('users.id', $id);
//        $builder->where('sms_verification.hash', $hash);
        $builder->where('sms_verification.certification', $certification);
        $query = $builder->get();
        $result = $query->getRow();
        return $result->id;
    }

    public function update_pass($id, $password)
    {
        try {
            $builder = $this->db->table('users');
            $builder->set('password', $password);
            $builder->set('updated_pass', date('Y-m-d H:i:s'));
            $builder->where('id', $id);
            $builder->update();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function overlap($id, $division)
    {
        if ($division == 'id') {
            $builder = $this->db->table('users');
        } else {
            $builder = $this->db->table('users_info');
        }
        $builder->selectCount($division);
        $builder->Where($division, $id);

        $query = $builder->get();
        $result = $query->getRow();

        if ($division == 'id') {
            return $result->id;
        } else {
            return $result->nickname;
        }
    }


    public function sms_request($phone, $rand_num, $divison)
    {
        $now = date("Y-m-d H:i:s");
        $ymd = date("Ymd");
        $hms = date('His');

        $data = array(
            'phone' => $phone,
            'certification' => $rand_num,
            'ymd' => $ymd,
            'hms' => $hms,
            'status' => 0, // default 0
            'division' => $divison
        );

        $builder = $this->db->table('sms_verification');
        try {
            $builder->insert($data);
            $result['last_id'] = $this->db->insertID();

            $builder = $this->db->table('sms_verification');
            $builder->where('idx', $result['last_id']);
            $query = $builder->get();
            $result['data'] = $query->getRow();
            return $result;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function sms_confirm($phone, $certification)
    {
        $builder = $this->db->table('sms_verification');
        $builder->select('certification,ymd,hms');
        $builder->Where('phone', $phone);
        $builder->Where('certification', $certification);
        $builder->Where('status', 0);
        $query = $builder->get();
        return $query->getResult();
    }

    public function sms_log($phone)
    {
        $query = "INSERT INTO sms_log (phone,count) VALUES('" . $phone . "',1) 
                    ON DUPLICATE KEY UPDATE count = count+1 ";
        $this->db->query($query);
    }

    public function sms_count($phone)
    {
        $builder = $this->db->table('sms_verification');
        $builder->selectCount('phone');
        $builder->Where('phone', $phone);
//        $builder->Where('status !=', 2);
        $builder->Where('ymd', $year = date('Ymd', time()));
        $query = $builder->get();
        $result = $query->getRow();
        if ($result) {
            return $result->phone;
        } else {
            return 1;
        }
    }

    public function img_upload($data)
    {
        $builder = $this->db->table('files');
        $builder->insert($data);
        $result = $this->db->insertID();
        return $result;
    }

    public function img_delete($id, $img_division)
    {
        try {
            $builder = $this->db->table('files');
            $builder->where('u_id', $id);
            $builder->where('division', $img_division);
            $builder->delete();
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
        $builder->where('division',0);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function gallery_img_delete($remove,$id)
    {
        try {
            $builder = $this->db->table('files');
            $builder->whereIn('f_idx',$remove);
            $builder->where('u_id',$id);
            $builder->where('division',0);
            $builder->delete();
            return 'success';
        } catch (Exception $exception) {
            return $exception;
        }
    }


    public function img_all($id)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id', $id);
        $query = $builder->get();
        $result = $query->getResultArray();
        return $result;
    }

    public function img_get($id, $img_division)
    {
        $builder = $this->db->table('files');
        $builder->where('u_id', $id);
        $builder->where('division', $img_division);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }

    public function device_insert($fcm_token, $os, $u_idx)
    {
        $query = "INSERT INTO device (fcm_token,os,u_idx) VALUES('" . $fcm_token . "','" . $os . "','" . $u_idx . "') 
                    ON DUPLICATE KEY UPDATE fcm_token= '$fcm_token' ";
        try {
            $this->db->query($query);
            $result['last_id'] = $this->db->insertID();

            $builder = $this->db->table('sms_verification');
            $builder->where('idx', $result['last_id']);
            $query = $builder->get();
            $result['data'] = $query->getRow();
            return $result;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function block_list($id)
    {
        return 'block _ test';
    }

    public function connect_users($list)
    {
        $builder = $this->db->table('users');
        $builder->select('users.idx, users.id, users_info.nickname');
        $builder->join('users_info', 'users.id = users_info.uid', 'left');
        $builder->whereIn('idx', $list);
        $query = $builder->get();
        return $result = $query->getResultArray();
    }

    public function nickname_change($nick,$token)
    {
        $builder = $this->db->table('users');
        $builder->select('users.id, users_info.nickname');
        $builder->join('users_info','users.id = users_info.uid', 'left');
        $builder->where('token',$token);
        $query = $builder->get();
        $result_id = $query->getRow();

        if(!$result_id){
            return array('fail');
        }

        $uid = $result_id->id;
        $oldnick = $result_id->nickname;

        if($uid) {
            try {
                $builder = $this->db->table('users_info');
                $builder->set('nickname', $nick);
                $builder->where('uid', $uid);
                $builder->update();
                return array('success', 'oldnick'=>$oldnick, 'newnick'=> $nick);
            } catch (Exception $exception) {
                return $exception;
            }
        }else{
            return array('fail');
        }
    }

    public function likeSubmit_chk($arg)
    {
        $builder = $this->db->table('best');
        $builder->where($arg);
        return $builder->countAllResults();
    }

    public function likeSubmit($data)
    {
        $builder = $this->db->table('best');
        $builder->insert($data);
        $result['last_id'] = $this->db->insertID();

        return $result;
    }

    public function ranking($data)
    {
        $sql = "SELECT like_id,  COUNT(like_id) as counting,dense_rank() OVER(ORDER BY COUNT(like_id) DESC) as ranking
                FROM best where year = ? and month = ? and day = ? group by like_id order by counting desc";

        $sql = "SELECT like_id,  COUNT(like_id) as counting,dense_rank() OVER(ORDER BY COUNT(like_id) DESC) as ranking ,
                                            concat('https://files.ntalk.me/profile/',file_url_real,f_tempname) as profile,
                                            nickname,gender,age
                                            FROM best a left join files b on a.like_id = b.u_id and division = 1
                                            left join users_info c on a.like_id = c.uid left join users d on a.like_id = d.id
                                            where a.year = ".$data[0]." and a.month = ".$data[1]." and a.day = ".$data[2]." group by a.like_id order by counting desc";
        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function getUserInfoCount($id)
    {
        $builder = $this->db->table('best');
        $builder->where('like_id',$id);
        $result = $builder->countAllResults();
        return $result;
    }

    public function updateSocialToken($id,$access_token, $refresh_token = null)
    {
        try {
            $builder = $this->db->table('users');
            if ($access_token) {
                $builder->set('access_token', $access_token);
            }
            if ($refresh_token) {
                $builder->set('refresh_token', $refresh_token);
            }
            $builder->where('id', $id);
            $result = $builder->update();
            return $result->resultID;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getHashId($hash)
    {
        return $this->select('id')->where('hash',$hash)->first();
    }

    public function cutout_chk($id,$cut_id)
    {
        $builder = $this->db->table('users_cutout');
        $builder->where('id',$id);
        $builder->where('cut_id',$cut_id);
    }

    public function insert_cutout($data)
    {
        try {
            $builder = $this->db->table('users_cutout');
            $result = $builder->insert($data);
            return $result->resultID;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getCutoutUser($id)
    {
        $builder = $this->db->table('users');
        $builder->select('users.id,users.gender,users_info.age,
                                users_info.nickname,users_info.location,
                                users_info.location2,
                                concat(\'https://files.ntalk.me/profile/\',file_url_real,f_tempname) as profile_real,
                                concat(\'https://files.ntalk.me/profile/\',file_url_thumb,f_tempname) as profile_thumb
                                '
                );
        $builder->join('users_info','users.id = users_info.uid','left');
        $builder->join('files','users.id = files.u_id and division = 1','left');
        $builder->where('id',$id);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;

    }

    public function getCutoutList($id)
    {
        $builder = $this->db->table('users_cutout');
        $builder->where('id',$id);
//        $builder->orWhere('cut_id',$id);
        $query = $builder->get();
        $result = $query->getResult();

        return $result;
    }
}

