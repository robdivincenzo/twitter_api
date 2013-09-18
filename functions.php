<?php
// transform tweet text urls into actual html anchor tags
function twitterify($ret) {
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
	$ret = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
	$ret = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $ret);
	return $ret;
}

//Grab tweets using the new Twitter API 1.1 with OAUTH
function get_tweets( $count = 8 ) {	
	$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
	
	// Set our super secret stuff
	$oauth_access_token = "***************oursecretstuff!***************";
	$oauth_access_token_secret = "***************oursupersecretstuff!***************";
	$consumer_key = "***************dontevenaskitssosecret!***************";
	$consumer_secret = "***************afourthsecretauthenticationkey***************";
	
	//set our oauth parameters
	$oauth_array = array( 
			'count' => $count,
			'oauth_consumer_key' => $consumer_key,
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $oauth_access_token,
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0');
	
	$base_info = build_base_string($url, 'GET', $oauth);
	$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
	$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
	$oauth_array['oauth_signature'] = $oauth_signature;
	
	// build the authorization header
	$header = array( build_oauth_header($oauth_array), 'Expect:' );
	// set our curl options
	$ch_options = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_HEADER => false,
			CURLOPT_URL => $url . '?count='.$count,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false);
	//initialize curl
	$ch = curl_init();
	//set our curl options
	curl_setopt_array($ch, ch_options);
	//execute curl
	$json = curl_exec($ch);
	//close curl
	curl_close($ch);
	//decode the json object
	$data = json_decode($json);
	//return our twitter data!
	return $data;
}

// create the signature base string
function build_base_string($base_uri, $method, $params) {
	$r = array();
	ksort($params);
	foreach($params as $key=>$value){
		$r[] = "$key=" . rawurlencode($value);
	}
	return $method."&" . rawurlencode($base_uri) . '&' . rawurlencode(implode('&', $r));
}

// create the oauth header
function build_oauth_header($oauth_array) {
	$r = 'Authorization: OAuth ';
	$values = array();
	foreach($oauth_array as $key=>$value)
		$values[] = "$key=\"" . rawurlencode($value) . "\"";
	$r .= implode(', ', $values);
	return $r;
}
?>