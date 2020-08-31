<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Oauth  extends BaseConfig
{
    public $case = ['naver','google','kakao','facebook'];
    public $api = [
        'naver' => [
            'authorize' => 'https://nid.naver.com/oauth2.0/authorize',
            'token' => 'https://nid.naver.com/oauth2.0/token',
            'me' => 'https://openapi.naver.com/v1/nid/me',
//            'client_id' => 'iTMKNOqp4S5ptkg2eiC4',
//            'client_secret' => 'uM73EVwiIP',
            'client_id' => 'zSQDKi2O3AsqoXiAJl_f',
            'client_secret' => 'RGZp7356PQ',
            'callback' => 'http://ntalk.me/api/oauth_social/naverCallBack'
        ],
        'google' => [
            'authorize' => 'https://accounts.google.com/o/oauth2/v2/auth',
//            'client_id' => '734042945385-332gd9ao9rf7ag24l035sv61hicpm048.apps.googleusercontent.com',
//            'client_secret' => 'pDcxgYSiY6EvHNJfBwCKbE3C',
            'client_id' => '581011164598-mho4j7bnkgcdfdldiqtbhvq1sudnn23o.apps.googleusercontent.com',
            'client_secret' => 'Cr9DgIrzqB-g1wDPSaCGMDUS',
            'redirect_uri' => 'http://ntalk.me/api/oauth_social/googleCallback',
            'simple_api_key' => 'TEST'
        ],
        'kakao'  =>[
            'authorize' => 'https://kauth.kakao.com/oauth/authorize',
            'me' => 'https://kapi.kakao.com/v2/user/me',
            'logout' => 'https://kapi.kakao.com/v1/user/logout',
            'unlink' => 'https://kapi.kakao.com/v1/user/unlink',
            'token' => 'https://kauth.kakao.com/oauth/token',
//            'client_id' => '004a5ff0a93c531b8044b2ff97cbdeb3',
//            'client_secret' => 'i3TJJrgHGcSbPrArfGb9JW3vIWtbmvNr',
            'admin_key' =>'304ee032995d26264779ad76d38ccded',
            'client_id' => '5686e2d743ffad5fb6a69929323cdb12',
            'client_secret' => 'wIiBOKp541HTNRNAJph3h8KfWChCqmG0',
            'redirect_uri' => 'https://ntalk.me/api/oauth_social/kakaoCallback',
        ],
        'facebook' =>[
//            'app_id' => '3119073264831129',
//            'app_secret' => '10755945b0f5407da9706dc0f886e007',
            'app_id' => '2521853294701470',
            'app_secret' => '2d123c7dc6b6a8563e1b9a5e19484b7f',
            'redirect_uri' => 'https://ntalk.me/api/oauth_social/facebookCallback',
            'default_graph_version' => 'v2.10'
        ]
    ];
}