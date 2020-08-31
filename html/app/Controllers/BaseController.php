<?php
namespace App\Controllers;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 *
 * @package CodeIgniter
 */

use App\Models\Api\Oauth_m;
use App\Models\Api\Favorite_m;
use App\Models\Api\Mongo_m;
use CodeIgniter\Controller;

class BaseController extends Controller
{

	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = ['array','url','alert','date','form'];

	/**
	 * Constructor.
	 */
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.:
        $this->session = \Config\Services::session();
        $this->encrypter = \Config\Services::encrypter();
        $this->client = \Config\Services::curlrequest();
        $this->request = \Config\Services::request();
	}

    public function res($code, $message, $data = array())
    {
        switch ($code)
        {
            case 400 : $this->response->setStatusCode(400);
                break;
            case 403 : $this->response->setStatusCode(403);
                break;
            case 404 : $this->response->setStatusCode(404);
                break;
            case 406 : $this->response->setStatusCode(406);
                break;
            case 409 : $this->response->setStatusCode(409);
                break;
            case 500 : $this->response->setStatusCode(500);
                break;
        }
        $data = (object)$data;
        $res = array(
            'code'    => (int)$code,
            'message' => $message,
            'data'    => $data
        );
        return $res;
    }

    public function user_authentication($token = null,$type)
    {
        $model = new Oauth_m();
        if($type == 'app') {
            if($token) {
                $user_info = $model->token_user_info($token);
            }else{
                $user_info = "";
            }
        }else{
            if($this->session->get('user_details')) {
                $idx = json_decode($this->session->get('user_details'))->idx;
                $user_info = $model->idx_user_info($idx);
            }else{
                $user_info = $model->idx_user_info($this->request->getGetPost('idx'));
            }
        }

        return $user_info;
    }

    /**
     * 즐겨찾기
     * @return favorite user
     */
    public function favoriteUserList($id)
    {
        $model = new Favorite_m();
        $list = $model->getFavoriteUserList($id);

        $arr = array();
        foreach ($list as $lt){
            $list = $model->favorite_userinfo($lt['favorite_id']);
            array_push($arr,$list);
        }
        return $arr;
    }

    public function insertUserLog($token = null)
    {
        $this->model = new Oauth_m();
        $this->mongo_m = new Mongo_m();
        $now = date('ymd');
        $agent = $this->request->getUserAgent();

        if(strpos($agent,'NTalk Android') !== false){
            $id = $this->request->getGetPost('id');
            $user_info = $this->model->user_info($id);

            $arr_agent = explode(' ',$agent->getAgentString());
            $appVersion = $arr_agent[2];
            $is_mobile = 'true';
            $platform = 'Android';
            $version = $appVersion;
        }else if($this->session->get('idx')){
            $user_info = $this->model->idx_user_info($this->session->get('idx'));
            $is_mobile = $agent->isMobile();
            $platform = $agent->getPlatform();
            $version = $agent->getVersion();
        }else{
            $is_mobile = $agent->isMobile();
            $platform = $agent->getPlatform();
            $version = $agent->getVersion();
            $user_info = new \stdClass;
            $user_info->idx = 'GUEST';
        }


        //로그 1일 중복 체크
        if($user_info) {
            $todayLogChk = $this->mongo_m->reduplicationLog($user_info->idx, $now, $this->request->getIPAddress());
            if($todayLogChk < 1){

                $data = array(
                    'idx' => $user_info->idx,
                    'direct' => $this->request->getGetPost('direct'),
                    'mobile' => $is_mobile,
                    'analysis' => $this->request->getGetPost('analysis'),
                    'ip' => $this->request->getIPAddress(),
                    'platform' => $platform,
                    'browser' => $agent->getBrowser(),
                    'version' => $version,
                    'agent' => $agent->getAgentString(),
                    'referrer' => $agent->getReferrer(),
                    'current_url' => $this->request->getGetPost('current_url'),
                    'domain' => $this->request->getGetPost('domain'),
                    'y' => date('y',time()),
                    'ym' => date('ym',time()),
                    'ymd' => date('ymd',time()),
                    'h' => date('h',time()),
                    'his' => date('his',time()),
                );

                $result = $this->mongo_m->insertUserLog($data);
            }
        }
    }

    public function cutout_list($nickname)
    {
        $this->model = new Oauth_m();
        $id = $this->model->getUserInfo($nickname)->uid;

        $data = $this->model->getCutoutList($id);

        $arr = array();
        foreach ($data as $dt) {
            $toData = $this->model->getCutoutUser($dt->cut_id);
            array_push($arr,$toData);
        }
        return $arr;
    }

}
