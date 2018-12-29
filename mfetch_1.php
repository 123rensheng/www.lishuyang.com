<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2018/7/16
 * Time: 20:35
 */



function mfetch($params = array(), $method){
    $mh = curl_multi_init();
    $handles = array();

    foreach($params as $key => $param){
        $ch = curl_init();
        $url = $param['url'];
        $data = $param['params'];
        if(strtolower($method) === 'get'){
            $url = "$url?".http_build_query($data);
        }else{
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        $handles[$ch] = $key;
    }

    $running = null;
    $curls = array();
    do{
        usleep(10000);
        curl_multi_exec($mh, $running);
        while(($ret = curl_multi_info_read($mh)) !== false){
            $curls[$handles[$ret["handle"]]] = $ret;
        }
    }while($running > 0);

    foreach($curls as $key => &$val){
        $val["content"] = curl_multi_getcontent($val['handle']);
        curl_multi_remove_handle($mh, $val['handle']);
    }

    curl_multi_close($mh);
    ksort($curls);
    return $curls;
}

$keyword = "360";
$page = 1;
$params = array();

for($i = 0; $i < 10; $i++){
    $params[$i] = array(
        "url" => "http://www.baidu.com/s",
        "params" => array('q' => $keyword, 'ie' => 'utf-8', 'pn' => ($page - 1) * 10 + $i + 1),
    );
}

$ret = mfetch($params, 'GET');

print_r($ret);