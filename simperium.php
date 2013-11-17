<?php
/*
	Wrapper for the Simperium.com API.

*/
class Simperium{
	private $app_name;
	private $api_key;
	private $auth_url = 'https://auth.simperium.com';
	private $api_url = 'https://api.simperium.com';
	private $bucket;
	private $user;
	private $token;
	public function __construct($app_name,$api_key){
		//	authorize connection
		$this->app_name = $app_name;
		$this->api_key = $api_key;
	}
//	set the bucket to use by the variable passed
	public function __get($bucket){
		$this->bucket = strtolower( $bucket );
		return $this;
	}
//	user functions
	public function authorize($username,$password){
		$headers = array(
			'X-Simperium-API-Key: '.$this->api_key
		);
		$url = $this->auth_url.'/1/'.$this->app_name.'/authorize/';
		$data = array(
			'username'=>$username,
			'password'=>$password
		);
		$ret = $this->_post($url,$data,$headers);
		$this->user = $ret;
		$this->token = (String) $ret->access_token;

		return $this->token;
	}
	
	public function get_token(){
		return $this->token;
	}

	public function create($username, $password){
		$headers = array(
			'X-Simperium-API-Key: '.$this->api_key
		);
		$url = $this->auth_url.'/1/'.$this->app_name.'/create/';
		$data = array(
			'username'=>$username,
			'password'=>$password
		);
		$ret = $this->_post($url,$data,$headers);
		if( !$ret ){
			//	user already exists, attempt to authorize instead
			return $this->authorize($username,$password);
		}else{
			$this->user = $ret;
			$this->token = $ret->access_token;
			return $this->token;
		}
	}

	public function reset_password($username, $new_password){
		$headers = array(
			'X-Simperium-API-Key: '.$this->api_key
		);
		$url = $this->auth_url.'/1/'.$this->app_name.'/reset_password/';
		$data = array(
			'username'=>$username,
			'new_password'=>$password
		);
		$ret = $this->_post($url,$data,$headers);
		$this->_debug( $ret );
	}

	public function update($username, $password,$new_username='',$new_password=''){
		$headers = array(
			'X-Simperium-API-Key: '.$this->api_key
		);
		$url = $this->auth_url.'/1/'.$this->app_name.'/update/';
		$data = array(
			'username'=>$username,
			'password'=>$password
		);
		if( $new_username != '' ){
			$data['new_username'] = $new_username;
		}
		if( $new_password != '' ){
			$data['new_password'] = $new_password;
		}
		$ret = $this->_post($url,$data,$headers);
		$this->_debug( $ret );
	}

//	bucket / document functions
	public function insert($data){
		$uuid = $this->generate_uuid();
		return $this->post( $uuid, $data );
	}
	public function post($uuid,$data){
		$headers = array(
			'X-Simperium-Token: '.$this->token
		);
		$url = $this->api_url.'/1/'.$this->app_name.'/'.$this->bucket.'/i/'.$uuid;
		return $this->_post($url,$data,$headers);
	}

	public function get($uuid){
		$headers = array(
			'X-Simperium-Token: '.$this->token
		);
		$url = $this->api_url.'/1/'.$this->app_name.'/'.$this->bucket.'/i/'.$uuid;
		$ret = $this->_get($url,array(),$headers);
		return $ret;
	}
	
	public function buckets(){
		$headers = array(
			'X-Simperium-Token: '.$this->token
		);
		$url = $this->api_url.'/1/'.$this->app_name.'/buckets/';
		$ret = $this->_get($url,array(),$headers, true);
		$this->_debug( $ret );
	}

	public function delete($uuid){
		$headers = array(
			'X-Simperium-Token: '.$this->token
		);
		$url = $this->api_url.'/1/'.$this->app_name.'/'.$this->bucket.'/i/'.$uuid;
		$ret = $this->_delete($url,array(),$headers);
		$this->_debug( $ret );		
	}

	public function index($data = false, $mark= "", $limit= "", $since=""){
		$headers = array(
			'X-Simperium-Token: '.$this->token
		);
		$args = array();
		if( $data ){
			$args['data'] = 1;
		}
		if( $mark ){
			$args['mark'] = $mark;
		}
		if( $limit ){
			$args['limit'] = $limit;
		}
		if( $since ){
			$args['since'] = $since;
		}
		$url = $this->api_url.'/1/'.$this->app_name.'/'.$this->bucket.'/index';
		return $this->_get($url,$args,$headers);
	}
	
//	private utility functions
	public function _debug($str){
		echo '<pre>'.print_r($str,true).'</pre>';
	}
	private function _get($url,$fields=array(),$headers=array(), $debug = false){
		$ch = curl_init();
		$timeout=5;
		if( count($fields) ){
			$fields_string = '';
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; } 
			rtrim($fields_string,'&');
			$url = $url."?".$fields_string;
		}
#		echo $url.'<br />';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$output = curl_exec($ch);
		curl_close($ch);
		return json_decode($output);
	}	

	private function _post($url,$data=array(),$headers=array(),$debug = false){
		$ch = curl_init();
		$timeout=5;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		$output = curl_exec($ch);
		curl_close($ch);

		return json_decode($output);
	}

	private function _delete($url,$headers = array()){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		$output = curl_exec($ch);
		curl_close($ch);
		return json_decode($output);
	}

	public function generate_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
}

/*

	$headers = array(
	    "Content-Type: application/json",
	    "X-Parse-Application-Id: " . $config['parse']['appid'],
	    "X-Parse-REST-API-Key: " . $config['parse']['restkey']
	);
	$data = array(
	    'username' => $username,
	    'password' => $password,
	);
	$url = 'https://api.parse.com/1/users';
	$ch = curl_init();
	$timeout=5;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	$output = curl_exec($ch);
	curl_close($ch);
	return json_decode($output);

*/