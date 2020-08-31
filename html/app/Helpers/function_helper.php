<?php

if (! function_exists('phone_regex')) {
    function phone_regex($phone)
    {
        if (preg_match("/^01([0|1|6|7|8|9]?)?([0-9]{3,4})?([0-9]{4})$/", $phone)) {
            return true;
        }else{
            return false;
        }
    }
}

if (! function_exists('id_regex')) {
    /**
     * @param $id
     * @return bool
     * 영문숫자 시작(최소1글자) 로 구성된 5글자이상 12글자 이하 만 허용
     */
    function id_regex($id)
    {
        //^([a-z]{1})(?=.*[a-z0-9+]).{6,10}$
//        if (preg_match("/^[_a-zA-Z0-9-\.]+@[\.a-zA-Z0-9-]+\.[a-zA-Z]+$/", $id)) {
//            if (preg_match("/^([a-z]{1})?([a-z0-9+]{6,10})\$/u", $id)) {
//        if (preg_match("/^[a-zA-Z][a-zA-Z0-9]{5,9}$/u", $id)) {
        if (preg_match("/^[A-Za-z0-9+]{6,10}$/u", $id)) {
            return true;
        }else{
            return false;
        }
    }
}

if (! function_exists('password_regex')) {
    /**
     * @param $password
     * @return bool
     * 최소 8자리이상 12글자 이하 대문자 1자리 소문자 1자리 숫자 1자리 특수문자 1자리 가 포함된 구성
     */
    function password_regex($password)
    {
//        if (preg_match("/^(?=.\d{1,})(?=.[~`!@#$%\^&()-+=]{1,})(?=.[a-z]{1,})(?=.*[A-Z]{1,}).{8,14}$/", $password)) {
//        if (preg_match("/^.*(?=^.{8,14}$)(?=.*\d{1,})(?=.*[a-z]{1,})(?=.*[A-Z]{1,})(?=.*[!@#$%^&+=]).*$/u", $password)) {
//        if (preg_match("/^(?=.*[a-zA-Z])(?=.*[!@#$%^&*+=-])(?=.*[0-9]).{8,12}$/u", $password)) {
        if (preg_match("/^[a-zA-Z0-9~`!@#$%\\^&*()-]{8,16}$/u", $password)) {
            return true;
        }else{
            return false;
        }
    }
}

if (! function_exists('nick_regex')) {
    function nick_regex($nick)
    {
        if (preg_match("/^[a-zA-Zㄱ-힣0-9★☆○●◇◆□■㈜™㉿㉾:hotsprings:]{2,10}$/u", $nick)) {
            return true;
        }else{
            return false;
        }
    }
}

if (! function_exists('onlyHanAlpha')) {
    function onlyHanAlpha($subject)
    {
        $pattern = '/([\xEA-\xED][\x80-\xBF]{2}|[a-zA-Z])+/';
        preg_match_all($pattern, $subject, $match);
        return implode('', $match[0]);
    }
}

if (! function_exists('merge_sort')) {
    function merge_sort($my_array)
    {
        if(count($my_array) == 1 ) return $my_array;
        $mid = count($my_array) / 2;
        $left = array_slice($my_array, 0, $mid);
        $right = array_slice($my_array, $mid);
        $left = merge_sort($left);
        $right = merge_sort($right);
        return merge($left, $right);
    }
}

if (! function_exists('merge')) {
    function merge($left, $right)
    {
        $res = array();
        while (count($left) > 0 && count($right) > 0) {
            if ($left[0] > $right[0]) {
                $res[] = $right[0];
                $right = array_slice($right, 1);
            } else {
                $res[] = $left[0];
                $left = array_slice($left, 1);
            }
        }
        while (count($left) > 0) {
            $res[] = $left[0];
            $left = array_slice($left, 1);
        }
        while (count($right) > 0) {
            $res[] = $right[0];
            $right = array_slice($right, 1);
        }
        return $res;
    }
}

if (! function_exists('post')) {
// POST 방식 함수
    function post($url, $fields)
    {
        $post_field_string = http_build_query($fields, '', '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field_string);
        curl_setopt($ch, CURLOPT_POST, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
if (! function_exists('hyphen_hp_number')) {
    function hyphen_hp_number($hp)
    {
        $hp = preg_replace("/[^0-9]/", "", $hp);
        return preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $hp);
    }
}

// post함수 호출(fcm)
//post('https://haon.ntalk.me:8443/fcm_send', array('field1'=>'value1', 'field2'=>'value2'));

