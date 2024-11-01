<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for plugin authorization
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Authorization
{
	/**
	 * Authorization server login form endpoint
	 */
	const AUTHORIZATION_ENDPOINT_LOGIN = 'https://login.wedos.com/oauth2/auth';

	/**
	 * Authorization server register form endpoint
	 */
	const AUTHORIZATION_ENDPOINT_REGISTER = 'https://login.wedos.com/oauth2/register';

	/**
	 * Client ID
	 */
	const AUTHORIZATION_CLID = '71ae4d853e90f8189e8a992645f8a70b57f30be4ef04653e6f11ab6c2a119a8f62f4199efd5146cb';

	/**
	 * Scope
	 */
	const AUTHORIZATION_SCOPE = 'dcr';

	/**
	 * Response type
	 */
	const AUTHORIZATION_RESPONSE_TYPE = 'code';

	/**
	 * Authorization server token endpoint
	 */
	const TOKEN_ENDPOINT = 'https://login.wedos.com/oauth2/token';

	/**
	 * Grand type
	 */
	const TOKEN_GRANT_TYPE = 'authorization_code';

	/**
	 * Challenge method - none
	 */
	const CHALLENGE_METHOD_NONE = 'none';

	/**
	 * Challenge method - sha256
	 */
	const CHALLENGE_METHOD_SHA256 = 'sha256';

	/**
	 * Authorization server timeout
	 */
	const AUTHORIZATION_SERVER_TIMEOUT = 30;

	/**
	 * Plugin object
	 *
	 * @since 1.0.0
	 * @var Wgpwpp
	 */
	public Wgpwpp $plugin;

	/**
	 * Verification controller
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Verification
	 */
	public Wgpwpp_Verification $verification;

	/**
	 * Last response from authorization server
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $last_response = [];


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param Wgpwpp $plugin objekt pluginu
	 * @return void
	 */
	public function __construct(Wgpwpp $plugin)
	{
		$this->plugin = $plugin;

		$this->load_dependencies();

		$this->define_hooks();
	}


	/**
	 * Load the required dependencies for admin section of this plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for plugin authorization
		 */
		$this->verification = new Wgpwpp_Verification($this->plugin, $this);
	}


	/**
	 * Register all of the hooks related to the authorization purposes.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks()
	{
		// redirection to login form
		$this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_auth_redirect', $this, 'authorization_redirect');

		// catch redirection back from authorization server and validate response
		$this->plugin->get_loader()->add_action('admin_init', $this, 'authorization_response_handler');
	}


	/**
	 * Checks if there are parameters from authorization server in current URL (after redirection back to admin panel)
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_authorization_response(): bool
	{
		return !is_null($this->get_authorization_response());
	}

	/**
	 * Validation of response parameters after redirection back from login form
	 *
	 * If validation is successful return authorization code
	 *
	 * @since 1.0.0
	 * @return string|null|WP_Error
	 */
	private function validate_authorization_response()
	{
		$response_data = $this->get_authorization_response();
		if (is_null($response_data))
			return NULL;

		if (is_wp_error($response_data))
    {
      $this->log(
        'Invalid response from authorization server',
        $response_data,
        Wgpwpp_Log::TYPE_ERROR
      );
      return $response_data;
    }

		$authorization_data = $this->get_authorization_data();
		if (!$authorization_data)
			return NULL;

		if ($response_data['state'] !== $authorization_data['state'])
    {
      $this->log(
        'Authorization data mismatch',
        ['Response data' => $response_data, 'Authorization data' => $authorization_data],
        Wgpwpp_Log::TYPE_ERROR
      );
      return NULL;
    }

		return $response_data['code'];
	}


	/**
	 * Returns array with authorization response parameters parsed from URL.
	 *
	 * Returns:
	 * - NULL if there are no authorization response parameters in the URL
	 * - WP_Error if user denied access for the plugin
	 *
	 * @return array|NULL|WP_Error
	 * @since 1.0.0
	 */
	private function get_authorization_response()
	{
		$error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
		if ($error === 'consent_required')
			return new WP_Error(1, __('Authorization failed. The user denied access for the plugin.', 'wgpwpp'));

		$code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
		$state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
		if (empty($code) || empty($state))
      return NULL;

		return [
			'code'  => $code,
			'state' => $state,
		];
	}


	/**
	 * Returns redirect URI for redirection to the authorization server (login form)
	 *
	 * @since 1.0.0
   * @param string $type sign in / sign up (default = in)
	 * @return string|WP_Error
	 */
	public function get_authorization_redirect_url(string $type = 'in')
	{
		$length = 16;
		$random = '';
		$state = $this->generate_state($length, $random);

		$pkce = $this->generate_challenge(64, self::CHALLENGE_METHOD_SHA256);
		if (is_wp_error($pkce))
			return $pkce;

	    $code = $this->generate_challenge($length, self::CHALLENGE_METHOD_NONE, $random);
	    if (is_wp_error($code))
	      return $code;

		$set = $this->set_authorization_data([
			'state'   => $state,
			'pkce'    => $pkce,
	        'code'    => $code,
	    ]);

		if (is_wp_error($set))
			return $set;

		$this->verification->set_verification_transient(['verification_method_name' => $random]);

		$parameters = [
			'client_id'             => self::AUTHORIZATION_CLID,
			'response_type'         => self::AUTHORIZATION_RESPONSE_TYPE,
			'redirect_uri'          => esc_url($this->plugin->get_admin_page_url()),
			'scope'                 => self::AUTHORIZATION_SCOPE,
			'state'                 => $state,
			'code_challenge'        => $pkce['challenge'],
			'code_challenge_method' => self::CHALLENGE_METHOD_SHA256,
		];

		array_walk($parameters, function(&$value, $key) { $value = $key.'='.$value; });

    $base_url = $type === 'in' ? self::AUTHORIZATION_ENDPOINT_LOGIN : self::AUTHORIZATION_ENDPOINT_REGISTER;

    return $base_url.'?'.implode('&', array_values($parameters));
	}


	/**
	 * Dynamic registration of OAuth2 client
	 *
	 * @since 1.0.0
	 * @param string $code authorization code
	 * @return void|WP_Error
	 */
	public function register_client(string $code)
	{
		$authorization_data = $this->get_authorization_data();

		$token = $this->get_token($code, $authorization_data['pkce']['verifier']);
		if (!$token)
			return new WP_Error(1, __('Failed to receive plugin registration token from authorization server!', 'wgpwpp'), $this->last_response);

		$client = $this->plugin->get_client()->register($token, get_locale());
		if (!$client)
			return new WP_Error(2, __('Failed to register OAuth2 client!', 'wgpwpp'));
	}

	/**
	 * Get token from authorization server based on authorization code
	 *
	 * @since 1.0.0
	 * @param string $code authorization code
	 * @param string $code_verifier PKCE verifier
	 * @return string|null
	 */
	private function get_token(string $code, string $code_verifier): ?string
	{
		$request_data = [
			'grant_type'    => self::TOKEN_GRANT_TYPE,
			'scope'         => self::AUTHORIZATION_SCOPE,
			'client_id'     => self::AUTHORIZATION_CLID,
			'code'          => $code,
			'redirect_uri'  => esc_url($this->plugin->get_admin_page_url()),
			'code_verifier' => $code_verifier,
		];

		// konfigurace požadavku
		$req_cfg = [
			'method'    => 'POST',
			'body'      => $request_data,
			'timeout'   => self::AUTHORIZATION_SERVER_TIMEOUT,
			'sslverify' => false,
		];

    $this->log('Requesting token for OAuth2 client registration', ['Request data' => $request_data]);

		// odeslání požadavku na API
		$response = wp_remote_post(self::TOKEN_ENDPOINT, $req_cfg);

		// chyba API volání
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200)
    {
      $this->log('Invalid response from authorization server', $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		$response_data = json_decode($response['body'], true);
		if (!$response_data)
    {
      $this->log('Invalid response from authorization server', $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		$this->last_response = $response_data;

		if ((int)$response_data['code'] !== 1000)
    {
      $this->log('Invalid response from authorization server', $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

    $this->log('Received token for OAuth2 client registration', $response_data['data']);

		return $response_data['data']['access_token'];
	}


	/**
	 * Returns name of transient for storing authorization metadata
	 *
	 * @since 1.0.0
	 * @return string|WP_Error
	 */
	private function get_authorization_transient_key()
	{
		// General check for user permissions.
		if (!current_user_can('manage_options'))
			return new WP_Error(1, 'Access denied!');

		return base64_encode(sprintf('%s-auth-data-%d', $this->plugin->get_plugin_name(), get_current_user_id()));
	}


	/**
	 * Store authorization metadata
	 *
	 * @since 1.0.0
	 * @param array|NULL $data authorization metadata
	 * @return void|WP_Error
	 */
	private function set_authorization_data(?array $data = NULL)
	{
		if (is_null($data))
		{
			delete_transient($this->get_authorization_transient_key());
			return;
		}

		if (!set_transient($this->get_authorization_transient_key(), $data))
			return new WP_Error(1, 'Failed to set authorization request data');
	}


	/**
	 * Returns authorization data for validation of authorization response
	 *
	 * @since 1.0.0
	 * @return array|null
	 */
	private function get_authorization_data(): ?array
	{
		$data = get_transient($this->get_authorization_transient_key());
		if (is_wp_error($data) || !$data)
    {
      $this->log('Failed to get plugin`s authorization data', $data, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		return $data;
	}

	/**
	 * Removes all authorization transients
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function clear_authorization_data()
	{
		$this->set_authorization_data();
		$this->verification->clear_verification_data();
	}


	/**
	 * Generates state parameter for authorization URL
	 *
	 * @since 1.0.0
	 * @param int $length length of random string
	 * @param string $random value of random string (reference)
	 * @return string
	*/
	private function generate_state(int $length, string &$random = ''): string
	{
		$user = wp_get_current_user();

		if (!$random)
    {
      $random = bin2hex(openssl_random_pseudo_bytes($length));
    }

		return $this->verification->generate_verification_header($user, $random);
	}


	/**
	 * Generate PKCE challenge
	 *
	 * @since 1.0.0.
	 * @param int $length length of generated random string
	 * @param string $challenge_method required encryption method for challenge generation
	 * @return array|WP_Error
	 */
	private function generate_challenge(int $length, string $challenge_method = self::CHALLENGE_METHOD_NONE, string $random = '')
	{
		if (!$random)
			$random = bin2hex(openssl_random_pseudo_bytes($length));

		$verifier = $this->base64url_encode($random);

		switch ($challenge_method)
		{
			case self::CHALLENGE_METHOD_NONE:
				$challenge = $this->base64url_encode($verifier);
				break;

			case self::CHALLENGE_METHOD_SHA256:
				$challenge = $this->base64url_encode(pack('H*', hash('sha256', $verifier)));
				break;

			default:
				return new WP_Error(1, 'Unsupported PKCE challenge method required!');
		}

		return [
			'random'            => $random,
			'verifier'          => $verifier,
			'challenge'         => $challenge,
			'challenge_method'  => $challenge_method,
		];
	}


	/**
	 * Base64url_encode
	 *
	 * @since 1.0.0
	 * @param string $random string to encode
	 * @return string
	 */
	private function base64url_encode(string $random): string
	{
		$base64 = base64_encode($random);
		$base64 = trim($base64, "=");
		$base64url = strtr($base64, '+/', '-_');
		return ($base64url);
	}


	/**
	 * Returns verification code data or NULL if there is no verification code generated
	 *
	 * @since 1.0.0
	 * @return array|null
	 */
	public function get_current_code(): ?array
	{
		$authorization_data = $this->get_authorization_data();
		if (!$authorization_data || !isset($authorization_data['code']))
			return NULL;

		if (!is_array($authorization_data['code']) || count($authorization_data['code']) !== 4)
			return NULL;

		return $authorization_data['code'];
	}


	/**
	 * Catch redirection back from login form and validate the response
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function authorization_response_handler()
	{
		// the request is not redirection from login form
		if ($this->plugin->get_client()->is_registered() || !$this->is_authorization_response())
			return;

		// validation of response parameters
		$code = $this->validate_authorization_response();
		if (is_null($code))
		{
      $this->log('Invalid authorization response', NULL, Wgpwpp_Log::TYPE_ERROR);
      $this->log('AUTHORIZATION FAILED', NULL, Wgpwpp_Log::TYPE_ERROR);
			$this->plugin->admin_section->notices->error(__('Invalid authorization response!', 'wgpwpp'), true);
			wp_redirect($this->plugin->get_admin_page_url());
			exit;
		}
		elseif (is_wp_error($code))
		{
      $this->log('Invalid authorization response', $code, Wgpwpp_Log::TYPE_ERROR);
      $this->log('AUTHORIZATION FAILED', NULL, Wgpwpp_Log::TYPE_ERROR);
			$this->plugin->admin_section->notices->error($code->get_error_message(), true);
			wp_redirect($this->plugin->get_admin_page_url());
			exit;
		}

    $this->log('Authorization response received', ['Authorization code' => $code]);

		// dynamic client registration
		$client = $this->register_client($code);
		if (is_wp_error($client))
		{
      $this->log('Failed to register OAuth2 client for plugin authorization', $client, Wgpwpp_Log::TYPE_ERROR);
      $this->log('AUTHORIZATION FAILED', NULL, Wgpwpp_Log::TYPE_ERROR);
			$this->plugin->admin_section->notices->error(__( 'Plugin registration failed', 'wgpwpp').' - '.$client->get_error_message(), true);
			wp_redirect($this->plugin->get_admin_page_url());
			exit;
		}

		$this->clear_authorization_data();

    $this->log('Clear plugin`s authorization data');
    $this->log('VERIFICATION SUCCESSFUL');
    $this->log('AUTHORIZATION SUCCESSFUL');

		$this->plugin->admin_section->notices->success( __( 'Plugin successfully registered', 'wgpwpp'), true);
		wp_redirect($this->plugin->get_admin_page_url());
		exit;
	}


	/**
	 * JS redirection to login form
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function authorization_redirect()
	{
    $this->log('AUTHORIZATION PROCESS BEGIN');

		// check nonce
		if (!check_ajax_referer( $this->plugin->get_nonce_name( 'wgpwpp_auth_redirect'), false, false))
    {
      $this->log('AJAX request failed - invalid WP nonce.', NULL, Wgpwpp_Log::TYPE_ERROR);
      $this->log('AUTHORIZATION PROCESS FAILED', NULL, Wgpwpp_Log::TYPE_ERROR);
      wp_die(json_encode(['result' => 'error', 'msg' => __('Invalid request!', 'wgpwpp')]));
    }

		// check if client is registered
		if ($this->plugin->get_client()->is_registered())
		{
      $this->log('OAuth2 client is already registered. Move to next step: '.$this->plugin->admin_section->layout->get_step());
      $this->log('AUTHORIZATION PROCESS DONE');
			// pass redirect URI to JS
			$result = [
				'result'    => 'success',
				'data'      => [
					'redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$this->plugin->admin_section->layout->get_step(),
				]
			];
			wp_die(json_encode($result));
		}

    // sing in / sign up
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'in';

		// get redirect URI
		$redirect_uri = $this->get_authorization_redirect_url($type);
		if (is_wp_error($redirect_uri))
    {
      $this->log('Failed to get redirect URI to login form of authorization server', $redirect_uri, Wgpwpp_Log::TYPE_ERROR);
      $this->log('AUTHORIZATION PROCESS FAILED', NULL, Wgpwpp_Log::TYPE_ERROR);
      wp_die(json_encode(['result' => 'error', 'msg' => $redirect_uri->get_error_message()]));
    }

		// pass redirect URI to JS
		$result = [
			'result'    => 'success',
			'data'      => [
				'redirect_uri' => $redirect_uri
			]
		];

    $this->log('Redirecting to login form of authorization server', $redirect_uri);
		wp_die(json_encode($result));
	}


  /**
   * Log for authorization process
   *
   * @param string $msg
   * @param WP_Error|mixed $data
   * @param string $type
   * @return void
   */
  private function log(string $msg, $data = NULL, string $type = Wgpwpp_Log::TYPE_INFO)
  {
    $msg = "\tAUTHORIZATION :: ".$msg;
    $this->plugin->log->write($msg, $data, $type);
  }
}