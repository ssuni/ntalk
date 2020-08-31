<?php namespace App\Controllers\Api;

use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use App\Libraries\Oauth;
use App\Models\Api\Oauth_m;
use App\Libraries\Jsonwebtotokens;
use Google_Client;
use Google_Service_Oauth2;
use Facebook\Facebook;


class Oauth_social extends BaseController
{
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->oauth = new Oauth();
        $this->model = new Oauth_m();
        $this->config = new \Config\Oauth();

        $this->gconfig = $this->config->api['google'];
        $this->kconfig = $this->config->api['kakao'];
        $this->fconfig = $this->config->api['facebook'];

        $this->google = new Google_Client();
        $this->google->setApplicationName("ntalk");
        $this->google->setClientId($this->gconfig['client_id']);
        $this->google->setClientSecret($this->gconfig['client_secret']);
        $this->google->setRedirectUri($this->gconfig['redirect_uri']);
        $this->google->setDeveloperKey($this->gconfig['simple_api_key']);
        $this->google->addScope("https://www.googleapis.com/auth/userinfo.email");

        $this->facebook = new Facebook([
            'app_id' => $this->fconfig['app_id'],
            'app_secret' => $this->fconfig['app_secret'],
            'default_graph_version' => $this->fconfig['default_graph_version']
        ]);
        $this->view = \Config\Services::renderer();
        helper('function,alert');
    }

    public function joinCheck()
    {
        $email = $this->request->getGetPost('email');
        $type = $this->request->getGetPost('type');

        $member_verification = $this->model->user_info($email);

        if ($member_verification) {
            if ($member_verification->type) {

            }
        }
    }

    // NAVER
    public function naverLogin()
    {
        $url = $this->oauth->login('naver');
        return redirect()->to($url);
    }

    public function naverCallBack()
    {

        $data = $this->oauth->verification('naver');
//        $member_verification = $this->model->user_info($data['response']['email']);
        $member_verification = $this->model->user_info('na@' . $data['response']['id']);

        if ($member_verification) {
            $type = $member_verification->type;

            if ($type !== 'naver') {
                alert('동일한 메일이 이미 존재합니다.', '/auth');
            }
        }

        if (!$data['response']['email']) {
            alert('소셜 계정에 이메일 정보가 없어 가입이 불가능 합니다.');
        }

        if ($member_verification) {
            if (password_verify($data['response']['id'], $member_verification->password)) {
                $info = array(
                    'idx' => $member_verification->idx,
                    'id' => $member_verification->id,
                    'token' => $member_verification->token,
                    'gender' => $member_verification->gender,
                    'age' => $member_verification->age,
                    'nickname' => $member_verification->nickname,
                    'location' => $member_verification->location,
                    'location2' => $member_verification->location2,
                    'access_token' => $member_verification->access_token,
                    'hash' => $member_verification->hash,
                    'type' => $member_verification->type
                );
                if ($member_verification->f_tempname) {
                    $info['profile_real'] = profileFolder . $member_verification->file_url_real . $member_verification->f_tempname;
                    $info['profile_thum'] = profileFolder . $member_verification->file_url_thumb . $member_verification->f_tempname;
                } else {
                    $info['profile_real'] = "";
                    $info['profile_thum'] = "";
                }
                $this->session->set($info);
                $this->session->set('user_details', json_encode($info));

                $ip = $this->request->getIPAddress();
                $this->model->last_login($member_verification->id);
                $this->model->last_login_ip($member_verification->id, $ip);
                $this->insertUserLog();
                return redirect()->to('/main');

            } else {
                alert('비밀번호가 일치하지 않습니다.');
            }
        } else {
            $jsonwebtokens = new Jsonwebtotokens();
            $GetSelect = array('login_id' => $data['response']['id']);

            $token = $jsonwebtokens->makeJWTEncode($GetSelect);


            $this->session->set('social', array(
//                    'email' => $data['response']['email'],
                    'email' => 'na@' . $data['response']['id'],
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'pk' => $data['response']['id'],
                    'type' => 'naver'
                )
            );

            return redirect()->to('/auth/terms');
            exit;
        }
    }

    public function naverUnlink()
    {
        $this->oauth->unlink('naver');
    }
    // NAVER

    // GOOGLE
    public function googleLogin()
    {
        $url = $this->google->createAuthUrl();
        return redirect()->to($url);
    }

    public function googleCallback()
    {
        $objOAuthService = new Google_Service_Oauth2($this->google);

        if (isset($_GET['code'])) {
            $this->google->authenticate($_GET['code']);
            $_SESSION['access_token'] = $this->google->getAccessToken();
        }

        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->google->setAccessToken($_SESSION['access_token']);
        }

        if ($this->google->getAccessToken()) {
            $userData = $objOAuthService->userinfo->get();
            if (!empty($userData)) {

//                $member_verification = $this->model->user_info($userData->email);
                $member_verification = $this->model->user_info('gl@' . $userData->id);

                if ($member_verification) {
                    $type = $member_verification->type;

                    if ($type !== 'google') {
                        alert('동일한 메일이 이미 존재합니다.', '/auth');
                    }
                }

                if ($member_verification) {
                    if (password_verify($userData->id, $member_verification->password)) {
                        $info = array(
                            'idx' => $member_verification->idx,
                            'id' => $member_verification->id,
                            'token' => $member_verification->token,
                            'gender' => $member_verification->gender,
                            'age' => $member_verification->age,
                            'nickname' => $member_verification->nickname,
                            'location' => $member_verification->location,
                            'location2' => $member_verification->location2,
                            'hash' => $member_verification->hash,
                            'access_token' => $member_verification->access_token,
                            'type' => $member_verification->type
                        );
                        if ($member_verification->f_tempname) {
                            $info['profile_real'] = profileFolder . $member_verification->file_url_real . $member_verification->f_tempname;
                            $info['profile_thum'] = profileFolder . $member_verification->file_url_thumb . $member_verification->f_tempname;
                        } else {
                            $info['profile_real'] = "";
                            $info['profile_thum'] = "";
                        }

                        $this->session->set($info);
                        $this->session->set('user_details', json_encode($info));

                        $ip = $this->request->getIPAddress();
                        $this->model->last_login($member_verification->id);
                        $this->model->last_login_ip($member_verification->id, $ip);
                        $this->insertUserLog();
                        $division = $this->request->getGet('app');

                        if ($division == 'app') {
                            echo 'app';
                            exit;
                        }

                        return redirect()->to('/main');
//                        alert('로그인 되었습니다.', '/main');
                    } else {
                        alert('비밀번호가 일치하지 않습니다.');
                    }
                } else {
                    $jsonwebtokens = new Jsonwebtotokens();
                    $GetSelect = array('login_id' => $userData->id);
                    $token = $jsonwebtokens->makeJWTEncode($GetSelect);
                    $gtoken['access_token'] = $this->google->getAccessToken();

                    $result = array(
                        'id' => $userData->email,
                        'password' => password_hash($userData->id, PASSWORD_DEFAULT),
                        'access_token' => $gtoken['access_token']['access_token'],
                        'type' => 'google',
                        'create_at' => date("Y-m-d H:i:s"),
                        'token' => $token['jwt'],
                        'hash' => md5(time()),
                    );

                    $this->session->set('social', array(
//                            'email' => $userData->email,
                            'email' => 'gl@' . $userData->id,
                            'access_token' => $gtoken['access_token']['access_token'],
                            'refresh_token' => null,
                            'pk' => $userData->id,
                            'type' => 'google'
                        )
                    );

                    return redirect()->to('/auth/sms');
                    exit;

                }
            }//데이터존재 유무
        } else {//Access token 유무
            alert('인증실패', '/auth');
        }
    }

    public function googleLogout()
    {
        $this->google->revokeToken($this->session->get('access_token'));
//        session_destroy();
//        alert('로그아웃 되었습니다.', '/auth');
    }
    // GOOGLE

    // KAKAO
    public function kakaoLogin()
    {
        $kakao_state = md5(microtime() . mt_rand()); // 보안용 값
        $_SESSION['kakao_state'] = $kakao_state;

        $query = array(
            'client_id' => $this->kconfig['client_id'],
            'client_secret' => $this->kconfig['client_secret'],
            'redirect_uri' => $this->kconfig['redirect_uri'],
            'response_type' => 'code',
            'state' => $kakao_state,
        );
        $url = $this->kconfig['authorize'] . '?' . http_build_query($query);

        return redirect()->to($url);
    }

    public function kakaoCallback()
    {
        if ($_SESSION['kakao_state'] != $_GET['state']) {
            alert_continue('잘못된 접근입니다.');
            return redirect()->to('/auth');
        }

        if ($this->request->getGet('code')) {

            $curl = \Config\Services::curlrequest();
            $code = $this->request->getGet('code');
            $token_api_url = $this->kconfig['token'];

            $response = $curl->request('POST', $token_api_url, [
                'allow_redirects' => false,
                'verify' => false,
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->kconfig['client_id'],
                    'redirect_uri' => $this->kconfig['redirect_uri'],
                    'code' => $code,
                    'client_secret' => $this->kconfig['client_secret'],
                ]
            ]);
            $data = json_decode($response->getBody(), true);

            $access_token = $data['access_token'];
            $refresh_token = $data['refresh_token'];
            $token_expires = $data['refresh_token_expires_in'];

            if ($access_token) {
                $curl = \Config\Services::curlrequest();
                $user_data_url = $this->kconfig['me'];
                $header_data[] = 'Authorization: Bearer ' . $access_token;

                $response_userdata = $curl->request('POST', $user_data_url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $access_token
                    ],
                    'allow_redirects' => true,
                    'verify' => false,
                ]);
                $userdata = json_decode($response_userdata->getBody(), true);

//                $member_verification = $this->model->user_info($userdata['kakao_account']['email']);
                $member_verification = $this->model->user_info('ka@' . $userdata['id']);

                if ($member_verification) {
                    $type = $member_verification->type;

                    if ($type !== 'kakao') {
                        alert('동일한 이미 존재합니다.', '/auth');
                    }
                }

                if ($member_verification) {
                    if (password_verify($userdata['id'], $member_verification->password)) {
                        $info = array(
                            'idx' => $member_verification->idx,
                            'id' => $member_verification->id,
                            'token' => $member_verification->token,
                            'gender' => $member_verification->gender,
                            'age' => $member_verification->age,
                            'nickname' => $member_verification->nickname,
                            'location' => $member_verification->location,
                            'location2' => $member_verification->location2,
                            'hash' => $member_verification->hash,
                            'access_token' => $access_token,
                            'type' => $member_verification->type
                        );
                        if ($member_verification->f_tempname) {
                            $info['profile_real'] = profileFolder . $member_verification->file_url_real . $member_verification->f_tempname;
                            $info['profile_thum'] = profileFolder . $member_verification->file_url_thumb . $member_verification->f_tempname;
                        } else {
                            $info['profile_real'] = "";
                            $info['profile_thum'] = "";
                        }

                        $this->session->set($info);
                        $this->session->set('user_details', json_encode($info));

                        $ip = $this->request->getIPAddress();
                        $this->model->last_login($member_verification->id);
                        $this->model->last_login_ip($member_verification->id, $ip);
                        $this->insertUserLog();

                        $this->model->updateSocialToken($info['id'], $access_token, $refresh_token);
                        return redirect()->to('/main');

                    } else {
                        alert('비밀번호가 일치하지 않습니다.');
                    }
                } else {
                    $jsonwebtokens = new Jsonwebtotokens();
                    $GetSelect = array('login_id' => $userdata['id']);
                    $token = $jsonwebtokens->makeJWTEncode($GetSelect);
                    // $gtoken['access_token'] = $this->google->getAccessToken();

                    $result = array(
                        'id' => 'ka@' . $userdata['id'],
                        'password' => password_hash($userdata['id'], PASSWORD_DEFAULT),
                        'access_token' => $access_token,
                        'refresh_token' => $refresh_token,
                        'type' => 'kakao',
                        'create_at' => date("Y-m-d H:i:s"),
                        'token' => $token['jwt'],
                        'hash' => md5(time()),
                    );

                    $this->session->set('social', array(
                            'email' => 'ka@' . $userdata['id'],
                            'access_token' => $access_token,
                            'refresh_token' => $refresh_token,
                            'pk' => $userdata['id'],
                            'type' => 'kakao'
                        )
                    );

                    return redirect()->to('/auth/terms');
                    exit;
                }
            }
        }
    }

    public function kakaoUserData($access_token)
    {
        $curl = \Config\Services::curlrequest();
        $user_data_url = $this->kconfig['me'];
        $header_data[] = 'Authorization: Bearer ' . $access_token;

        $response_userdata = $curl->request('POST', $user_data_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token
            ],
            'allow_redirects' => true,
            'verify' => false,
        ]);
        $userdata = json_decode($response_userdata->getBody(), true);

        if (!$userdata['kakao_account']['email']) {
            alert('이메일 정보를 확인할 수 없습니다.');
        }

        $member_verification = $this->model->user_info($userdata['kakao_account']['email']);

        if ($member_verification) {
            $type = $member_verification->type;

            if ($type !== 'kakao') {
                echo '동일한 메일이 이미 존재합니다.';
                exit;
            }
        }

        if ($member_verification) {
            if (password_verify($userdata['id'], $member_verification->password)) {
                $info = array(
                    'idx' => $member_verification->idx,
                    'id' => $member_verification->id,
                    'token' => $member_verification->token,
                    'gender' => $member_verification->gender,
                    'age' => $member_verification->age,
                    'nickname' => $member_verification->nickname,
                    'location' => $member_verification->location,
                    'location2' => $member_verification->location2,
                    'access_token' => $member_verification->access_token,
                    'hash' => $member_verification->hash,
                    'type' => $member_verification->type
                );
                if ($member_verification->f_tempname) {
                    $info['profile_real'] = profileFolder . $member_verification->file_url_real . $member_verification->f_tempname;
                    $info['profile_thum'] = profileFolder . $member_verification->file_url_thumb . $member_verification->f_tempname;
                } else {
                    $info['profile_real'] = "";
                    $info['profile_thum'] = "";
                }

                $this->session->set($info);
                $this->session->set('user_details', json_encode($info));

                alert('로그인 되었습니다.', '/main');
            } else {
                alert('비밀번호가 일치하지 않습니다.');
            }
        } else {
            $jsonwebtokens = new Jsonwebtotokens();
            $GetSelect = array('login_id' => $userdata['id']);
            $token = $jsonwebtokens->makeJWTEncode($GetSelect);
            $gtoken['access_token'] = $this->google->getAccessToken();

            $result = array(
//                'id'           => $userdata['kakao_account']['email'],
                'id' => 'ka@' . $userdata['id'],
                'password' => password_hash($userdata['id'], PASSWORD_DEFAULT),
                'access_token' => $access_token,
                'type' => 'kakao',
                'create_at' => date("Y-m-d H:i:s"),
                'token' => $token['jwt'],
                'hash' => md5(time()),
            );

            $this->session->set('social', array(
//                    'email' => $userdata['kakao_account']['email'],
                    'email' => 'ka@' . $userdata['id'],
                    'access_token' => $access_token,
                    'refresh_token' => null,
                    'pk' => $userdata['id'],
                    'type' => 'kakao'
                )
            );

            return redirect()->to('/auth/terms');
            exit;

        }
    }

    public function kakaoLogout($access_token)
    {
//        POST /v1/user/logout HTTP/1.1
//          Host: kapi.kakao.com
//          Authorization: Bearer {access_token}
        $curl = \Config\Services::curlrequest();
        $user_data_url = $this->kconfig['logout'];
        $header_data[] = 'Authorization: Bearer ' . $access_token;

        $response_userdata = $curl->request('POST', $user_data_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token
            ],
            'allow_redirects' => true,
            'verify' => false,
        ]);
        $userdata = json_decode($response_userdata->getBody(), true);
        session_destroy();
        alert('로그아웃 되었습니다.', '/auth');
    }

    public function kakaoUnlink()
    {

        $access_token = 'YgEasd1JqCBlUPYcDOfWjwFnviyS-UJ05tDyowo9dJkAAAFu-PwypQ';
        $curl = \Config\Services::curlrequest();
        $user_data_url = $this->kconfig['unlink'];

        $header_data[] = 'Authorization: Bearer ' . $access_token;

        $response_userdata = $curl->request('POST', $user_data_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token
            ],
//            'allow_redirects'  =>  true ,
            'verify' => false,
        ]);
        $userdata = json_decode($response_userdata->getBody(), true);

        var_dump($userdata);

        session_destroy();
//        alert('로그아웃 되었습니다.', '/auth');
    }
    // KAKAO

    // FACEBOOK

    public function facebookLogin()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        $permissions = ['email'];
        $url = $helper->getLoginUrl($this->fconfig['redirect_uri'], $permissions);
        return redirect()->to($url);
    }

    public function facebookCallback()
    {
        try {
            $helper = $this->facebook->getRedirectLoginHelper();
            $_SESSION['FBRLH_state'] = $_GET['state'];

            $accessToken = $helper->getAccessToken($this->fconfig['redirect_uri']);
            $_SESSION['access_token'] = $accessToken->getValue();

        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->facebook->setDefaultAccessToken($accessToken);
        }

        // Getting user's profile info from Facebook
        try {
            $graphResponse = $this->facebook->get('/me?fields=name,first_name,last_name,email,link,gender,picture');
            $fbUser = $graphResponse->getGraphUser();

//            var_dump($fbUser);
//            exit;

            if ($fbUser) {
//                if(array_key_exists('email', $fbUser)) {
//                    $member_verification = $this->model->user_info($fbUser['email']);
//                }else{
                $member_verification = $this->model->user_info('fb@' . $fbUser['id']);
//                }

                if ($member_verification) {
                    $type = $member_verification->type;

                    if ($type !== 'facebook') {
                        alert('동일한 메일이 이미 존재합니다.', '/auth');
                    }
                }

                if ($member_verification) {
                    if (password_verify($fbUser['id'], $member_verification->password)) {
                        $info = array(
                            'idx' => $member_verification->idx,
                            'id' => $member_verification->id,
                            'token' => $member_verification->token,
                            'gender' => $member_verification->gender,
                            'age' => $member_verification->age,
                            'nickname' => $member_verification->nickname,
                            'location' => $member_verification->location,
                            'location2' => $member_verification->location2,
                            'access_token' => $member_verification->access_token,
                            'hash' => $member_verification->hash,
                            'type' => $member_verification->type
                        );
                        if ($member_verification->f_tempname) {
                            $info['profile_real'] = profileFolder . $member_verification->file_url_real . $member_verification->f_tempname;
                            $info['profile_thum'] = profileFolder . $member_verification->file_url_thumb . $member_verification->f_tempname;
                        } else {
                            $info['profile_real'] = "";
                            $info['profile_thum'] = "";
                        }

                        $this->session->set($info);
                        $this->session->set('user_details', json_encode($info));

                        $ip = $this->request->getIPAddress();
                        $this->model->last_login($member_verification->id);
                        $this->model->last_login_ip($member_verification->id, $ip);
                        $this->insertUserLog();
                        return redirect()->to('/main');
                        alert('로그인 되었습니다.', '/main');
                    } else {
                        alert('비밀번호가 일치하지 않습니다.');
                    }
                } else {
                    $jsonwebtokens = new Jsonwebtotokens();
                    $GetSelect = array('login_id' => $fbUser['id']);
                    $token = $jsonwebtokens->makeJWTEncode($GetSelect);
//                    $gtoken['access_token'] = $this->google->getAccessToken();

                    $facebook_id = 'fb@' . $fbUser['id'];

                    $result = array(
                        'id' => $facebook_id,
                        'password' => password_hash($fbUser['id'], PASSWORD_DEFAULT),
                        'access_token' => $accessToken->getValue(),
                        'type' => 'facebook',
                        'create_at' => date("Y-m-d H:i:s"),
                        'token' => $token['jwt'],
                        'hash' => md5(time()),
                    );

                    $this->session->set('social', array(
                            'email' => $facebook_id,
                            'access_token' => $accessToken->getValue(),
                            'refresh_token' => null,
                            'pk' => $fbUser['id'],
                            'type' => 'facebook'
                        )
                    );

                    return redirect()->to('/auth/terms');
                    exit;

                }
            } else {
                alert_continue('로그인 오류');
                return redirect()->to('/auth');
            }

        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            session_destroy();
            // Redirect user back to app login page
            return redirect()->to('/auth');
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    public function facebookLogout()
    {
        $result = $this->facebook->delete('/me/permissions/', [], $this->session->get('access_token'));

        return $result;

        $helper = $this->facebook->getRedirectLoginHelper();
        $logoutUrl = $helper->getLogoutUrl($this->session->get('access_token'), 'https://ntalk.me');
        session_destroy();
        return redirect()->to($logoutUrl);
    }
    // FACEBOOK

}