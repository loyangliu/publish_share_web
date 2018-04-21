<?php

function url($uri) {
	if($uri && $uri[0]!='/') {
		$uri = '/' . $uri;
	}
	return BASE_URL . $uri;
}

function p(){
    echo '<pre>';
    foreach(func_get_args() as $item){
        print_r($item);
    }
}

function apiJson($code = 0, $msg = null, $data = null){
    return json_encode(compact('code', 'msg', 'data'), JSON_UNESCAPED_UNICODE);
}