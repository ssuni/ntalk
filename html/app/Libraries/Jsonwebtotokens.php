<?php namespace App\Libraries;
include APPPATH . 'ThirdParty/JWT/JWT.php';
use Firebase\JWT\JWT;
define('JWT_SECRET', '324jkl@$SFe%%2e2&*^');

class Jsonwebtotokens
{
    public function makeJWTEncode($GetSelect)
    {
        $result = array(
            'code' => 200,
            'status' => 'success',
            'message' => 'Valid login credentials.',
            'userid' => $GetSelect["login_id"]
        );

        $userid = $GetSelect["login_id"];
        $issuedAt = time();
        $expirationTime = $issuedAt + 60;  // jwt valid for 60 seconds from the issued time
        $payload = array(
            'userid' => $userid,
            'iat' => $issuedAt,
//            'exp' => $expirationTime
        );
        $key = JWT_SECRET;
        $alg = 'HS256';

        $jwt = JWT::encode($payload, $key, $alg);

        $result['jwt'] = $jwt;
        return $result;
    }

}