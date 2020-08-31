<?php

namespace App\Models\Api;

use CodeIgniter\Model;
use MongoDB;

class Mongo_m extends Model
{
    protected $client;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->client = new \MongoDB\Client();
        $this->collection = $this->client->ntalk->user_count_log;
    }

    //회원 로그 저장
    public function insertUserLog($data)
    {
        $insertOneResult = $this->collection->insertOne($data);
        return array($insertOneResult->getInsertedCount(),$insertOneResult->getInsertedId());
    }

    //회원 로그 중복 체크
    public function reduplicationLog($idx,$now,$ip)
    {
        $query = [
            'idx' => $idx,
            'ymd' => $now,
            'ip' => $ip
        ];
        $options = [
            'sort' => [
                'his' => -1
            ]
        ];
        $document = $this->collection->countDocuments($query,$options);
        return $document;
    }

    /**
     * @param $oldnick
     * @param $newnick
     * @return MongoDB\UpdateResult
     */
    public function nick_change_images($oldnick, $newnick)
    {
        $client = new \MongoDB\Client();
        // select a database
        $collection = $client->ntalk->chat_image_files;

        $userMatchCount = $collection->countDocuments(array('from' => $oldnick));

        if ($userMatchCount > 0) {
            try {
                $updateResult = $collection->updateMany(
                    ['from' => $oldnick],
                    ['$set' => ['from' => $newnick]]
                );
            } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
                echo 'Exception:', $e->getMessage(), "\n";
            }
        }

        return $updateResult;
    }

    /**
     * @param $oldnick
     * @param $newnick
     * @return int|null
     */
    public function nick_change_join_room($oldnick, $newnick)
    {
        $client = new \MongoDB\Client();
        $collection = $client->ntalk->chat_joined_rooms;

        $nickNameMatchCount = $collection->countDocuments(array('nickName' => $oldnick));
        $fromMatchCount = $collection->countDocuments(array('rooms.latestMessage.$.from' => $oldnick));
        $toMatchCount = $collection->countDocuments(array('rooms.$.latestMessage.$.to' => $oldnick));



//        var_dump($toMatchCount);
//        exit;

        $test = $collection->updateMany(
            ['rooms.latestMessage.to' => $oldnick],
            ['$set' => ['rooms.$.latestMessage.to' => $newnick]]
        );
        return $test->getModifiedCount();

        if($nickNameMatchCount > 0){
            $updateResult = $collection->updateMany(
                ['nickName' => $oldnick],
                ['$set' => ['nickName' => $newnick]]
            );
            return $updateResult->getModifiedCount();
        }

    }

    /**
     * @param $oldnick
     * @param $newnick
     */
    public function nick_change_reports($oldnick, $newnick)
    {

    }

    public function nick_change_room($oldnick, $newnick)
    {

    }

    public function nick_change_history($oldnick, $newnick)
    {
        $client = new \MongoDB\Client();
        // select a database
        $collection = $client->ntalk->chat_user_histories;

        $userMatchCount = $collection->countDocuments(array('users.nickName' => $oldnick));

        $query = [
            'users.nickName' => '회원1572945314',
        ];
        $options = [];
        $cursor = $collection->find($query, $options);

        $test = $collection->updateMany(
            ['users.nickName' => '21323213'],
            ['$set' => ['users.$.nickName' => $newnick]]
        );

        return 'test';
    }

    public function nick_change_firebase($oldnick, $newnick)
    {
        $client = new \MongoDB\Client();
        // select a database
        $collection = $client->ntalk->firebase;

        $userMatchCount = $collection->countDocuments(array('from' => $oldnick));

        if ($userMatchCount > 0) {
            try {
                $updateResult = $collection->updateMany(
                    ['nickName' => $oldnick],
                    ['$set' => ['nickName' => $newnick]]
                );
            } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
                echo 'Exception:', $e->getMessage(), "\n";
            }
        }

        return $updateResult;
    }

    public function nick_change_profiles($oldnick, $newnick)
    {
        $client = new \MongoDB\Client();
        // select a database
        $collection = $client->ntalk->user_profiles;

        $userMatchCount = $collection->countDocuments(array('nickName' => $oldnick));

        if ($userMatchCount > 0) {
            try {
                $updateResult = $collection->updateMany(
                    ['nickName' => $oldnick],
                    ['$set' => ['nickName' => $newnick]]
                );
            } catch (\MongoException $e) {
                echo 'Exception:', $e->getMessage(), "\n";
            }
        }

        return $updateResult;
    }


}
