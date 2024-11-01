<?php
/*
Plugin Name: wp-sso-client
Description: cliente sso
Version: 1.0
Author: ferro.mariano
Text Domain: wp-sso-client
*/



class wp_sso_client {
	
	static public $sso_server = '*';

	static $instance = null;
	static $volatil_data = array();
	public $current_user = null;

	function __construct() {
		$this->current_user = wp_get_current_user();
	}

	private function proccessRequest() {
		$data = $_REQUEST;
		if (!isset($data['data'])) {
			return;
		}
		wp_sso_client::set('is_valid_data', true);
		wp_sso_client::set('__request_data', $data['data']);
		foreach ($data['data'] as $key => $value) {
			wp_sso_client::set('__request_data_'.$key, $value);
		}
		

	}

	public function get_user_by($email, $userlogin) {
		$tmp = get_user_by('email', $email );
		if ($tmp instanceof WP_User) { return $tmp; }
		$tmp = get_user_by('login', $userlogin );
		if ($tmp instanceof WP_User) { return $tmp; }
		return false;
	}

	public function proccessToken() {
		$this->proccessRequest();
		if (!$this->isValidData()) { $this->set_error('100', 'invalid data'); return false; } 

		if ($this->current_user->exists())      { $this->print_json( apply_filters( 'wp_sso_client_login', array('ok') ) ); }
		if (!$this->getRequestData('is_login')) { $this->print_json( apply_filters( 'wp_sso_client_not_login_server', array('ok') ) ); }

		$responce_data = $this->request_token_server($this->getRequestData('user_token'));
		
		if (!$responce_data) { return false; }


		$user = $this->get_user_by($responce_data['data']['user_email'], $responce_data['data']['user_login']);
		if ($user == false) {

			$responce_data['data']['user_pass'] = md5(time().NONCE_KEY);
			 
			$user_id = wp_insert_user( $responce_data['data'] );
			if ( $user_id instanceof WP_Error ) {
				$this->set_error('101', 'no reg user'); 
				return false;


			}
			$user = get_user_by('ID', $user_id );
			
		}

	  add_filter( 'authenticate', array($this, 'filter_allow_login'), 10, 3 );    // hook in earlier than other callbacks to short-circuit them
    $user = wp_signon( array( 'user_login' => $user->user_login ) );
    remove_filter( 'authenticate', array($this, 'filter_allow_login'), 10, 3 );


		if ( $user instanceof WP_User ) {
			wp_set_current_user( $user->ID, $user->user_login );

			if ( is_user_logged_in() ) {
				$this->print_json(array('reload' => 1));
			} else {
				$this->set_error('102', 'invalid data'); return false;
			}
		}

		$this->set_error('103', 'invalid data'); return false;
	}

	public function filter_allow_login( $user, $username, $password ) {
    return get_user_by( 'login', $username );
  }

  public function genHashSecret($d) { return md5(http_build_query( $d ).WP_SSO_TOKEN); }

	public function request_token_server($data_token) {
		$url = wp_sso_client::$sso_server.'/sso/get_data/';

		$data_send = array('data' => base64_encode(json_encode($data_token)), 'public' => WP_SSO_SITE_ID );
		
		$hash = $this->genHashSecret($data_send);
		$data_send['hash'] = $hash;

		$fields    = http_build_query( $data_send );

		$ch = curl_init();

		// set URL and other appropriate options
		$options = array(
			CURLOPT_URL            => $url,
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => array('Content-Length: ' . strlen($fields)),
			CURLOPT_POSTFIELDS     => $fields,
      CURLOPT_HEADER         => false,
    );

		curl_setopt_array($ch, $options);

		$rs = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE); 

		if (curl_errno($ch)) { 
			$this->set_error('200', 'Error curl:'.curl_error($ch));
			curl_close($ch);
			return false;
    }
		curl_close($ch);


		if ($status != 200) { $this->set_error('201', 'Error curl: status not is 200, is '.$status); return false; }
		$rs = json_decode($rs, true);

		if (!is_array($rs)) { $this->set_error('202', 'Error response: json not array '); return false; }

		if (isset($rs['error'])) { $this->set_error($rs['error'], isset($rs['error_text']) ? $rs['error_text'] : '' ); return false; }


		return $rs;

	}

	public function isValidData() {
		return wp_sso_client::get('is_valid_data', false);
	}

	private function getRequestData($n) {
		return wp_sso_client::get('__request_data_'.$n);
	}

	public function set_error($n, $text='') { wp_sso_client::set('error_n', $n); wp_sso_client::set('error_text', $text); }
	public function get_error()             { return array( 'error'      => wp_sso_client::get('error_n', 0), 
																													'error_text' =>  wp_sso_client::get('error_text', ''), ); }
	public function print_error()           { $this->print_json( apply_filters( 'wp_sso_client_print_error', $this->get_error() ) ); }


	public function format_json($d) { return $d; }
	public function print_json($d)  { echo json_encode( $this->format_json($d) ); exit(); }



	static function get($n, $d=null) { return isset(self::$volatil_data[$n]) ? self::$volatil_data[$n] : $d;  }
	static function set($n, $v) { self::$volatil_data[$n] = $v; }

	static function getInstance() {
		self::genInstance();
		return self::$instance;
	}

	static function genInstance() {
		if (self::$instance) { return; }
		self::$instance = new wp_sso_client();
	}


	static function request_token() {
		if (!wp_sso_client::getInstance()->proccessToken()) {
			wp_sso_client::getInstance()->print_error();
		}
		exit();
	}

	static function load_scripts() {

		if (is_admin()) {
			return;
		}

		if (defined('WP_SSO_SERVER') && defined('WP_SSO_SITE_ID') && defined('WP_SSO_TOKEN')) {

			global $post;
			wp_enqueue_script(  'wp_sso_client', plugins_url( 'wp-sso-client.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_localize_script( 'wp_sso_client', 'wp_sso_client_urls', array( 'admin_ajax_url' => admin_url( 'admin-ajax.php' ), 'post_id' => intval( $post->ID ) ) );
			wp_enqueue_script(  'wp_sso_server', wp_sso_client::$sso_server.'sso/is_login?jsonp=1&public='.WP_SSO_SITE_ID.'&callback=callback_sso_server&_='.time(), array( 'jquery', 'wp_sso_client' ), '1.0', true );

		}


	}	
}

wp_sso_client::$sso_server = WP_SSO_SERVER;


add_action('wp_enqueue_scripts', array('wp_sso_client', 'load_scripts'), 100);

add_action( 'wp_ajax_sso_token',        array('wp_sso_client', 'request_token') );
add_action( 'wp_ajax_nopriv_sso_token', array('wp_sso_client', 'request_token') );

