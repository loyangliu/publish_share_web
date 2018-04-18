<?php

function url($uri) {
	if($uri && $uri[0]!='/') {
		$uri = '/' . $uri;
	}
	return BASE_URL . $uri;
}
