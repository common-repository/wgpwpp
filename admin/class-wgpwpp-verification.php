<?php

if (!defined('ABSPATH'))
  exit;


/**
 * Class - class responsible for verification of authorization request
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Verification extends Wgpwpp_REST_API
{
	/**
	 * Expiration of verification request in seconds
	 *
	 * @since 1.0.0
	 */
	const VERIFICATION_REQUEST_EXPIRATION = 3600;

	/**
	 * Authorization controller instance
	 *
	 * @since 1.0.0
	 * @var Wgpwpp_Authorization
	 */
	private Wgpwpp_Authorization $authorization;


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param Wgpwpp $plugin plugin instance
	 * @param Wgpwpp_Authorization $authorization plugins authorization controller instance
	 */
	public function __construct(Wgpwpp $plugin, Wgpwpp_Authorization $authorization)
	{
		parent::__construct($plugin);
		$this->authorization = $authorization;

		$this->define_hooks();
	}


	/**
	 * Register all of the hooks related to the verification purposes.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks()
	{
		// OAuth2 client has been already registered - no API endpoints required for verification
		if ($this->plugin->get_client()->is_registered())
			return;

		$this->plugin->get_loader()->add_action('rest_api_init', $this, 'register_endpoints');
	}


	/**
	 * Registers HTTP REST API endopints for verification purposes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_endpoints()
	{
		// verification was not be initialized
		$verification_data = $this->get_verification_transient();
		if (is_null($verification_data))
			return;

		// unknown verification method name
		$verification_method_name = $verification_data['verification_method_name'] ?? '';
		if (!$verification_method_name)
			return;

		register_rest_route($this->plugin->get_plugin_name().'/v1', '/'.$verification_method_name, [
			'methods'             => 'POST',
			'callback'            => [$this, 'verify_url'],
      'permission_callback' => '__return_true',
		]);

		register_rest_route( $this->plugin->get_plugin_name() . '/v1', '/verify', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'process_verification_request' ],
      'permission_callback' => '__return_true'
		]);

    $this->log('Verification endpoints registered', ['Verification_method_name' => $verification_method_name]);
	}


	/**
	 * Checks if site URL was verified before verification
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function is_url_verified(): bool
	{
		$data = $this->get_verification_transient();
		if (!$data || !isset($data['url_verified']) || !$data['url_verified'])
			return false;

		return true;
	}


	/**
	 * Generates verification header
	 *
	 * @since 1.0.0
	 * @param WP_User $user users object
	 * @param string $random random string
	 * @return string
	 */
	public function generate_verification_header($user, string $random): string
	{
    $state_data = json_encode([
      $this->plugin->get_plugin_name(),
      $random,
      $user->get('user_login'),
      $user->get('id'),
      time() + self::VERIFICATION_REQUEST_EXPIRATION,
      get_rest_url(),
    ]);

		$state = base64_encode($state_data);

		$chunk_length = strlen($state) / 3;
		if ((int)$chunk_length < $chunk_length)
			$chunk_length = (int)$chunk_length + 1;

		$state_arr = str_split($state, $chunk_length);

		return implode('-', $state_arr);
	}


	/**
	 * Validates request header and returns header data or NULL in case of error
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request request object
	 * @param array $response API response (reference)
	 * @return array|null
	 */
	private function validate_verification_request(WP_REST_Request $request, array &$response): ?array
	{
    // verify origin server IP address
    try
    {
      $this->verify_origin_ip_address();
    }
    catch (Exception $e)
    {
      $response = [
        'code'    => 2000,
        'msg'     => $e->getMessage(),
      ];
      $this->log('Failed to verify origin server IP address', $e->getMessage(), Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

    // verify authorization header
		$headers = $request->get_headers();
		if (!isset($headers['authorization']) || !is_array($headers['authorization']) || !count($headers['authorization']))
		{
      $this->log('Invalid verification request - missing authorization header', $headers, Wgpwpp_Log::TYPE_ERROR);

			$response = [
				'code'  => 2001,
				'msg'   => 'Missing authorization header!',
			];
			return NULL;
		}

		$code = trim(current($headers['authorization']));
		$code = str_replace('.', '', $code);
		$code = base64_decode($code);

		$data = json_decode($code);

    if (count($data) < 5)
		{
      $this->log('Invalid verification request - invalid verification code', $code, Wgpwpp_Log::TYPE_ERROR);
			$response = [
				'code'  => 2002,
				'msg'   => 'Invalid authorization header!',
			];
			return NULL;
		}

		if ($data[0] !== $this->plugin->get_plugin_name())
		{
      $this->log('Invalid verification request - invalid verification code', $code, Wgpwpp_Log::TYPE_ERROR);
			$response = [
				'code'  => 2002,
				'msg'   => 'Invalid authorization header!',
			];
			return NULL;
		}

		if (time() > $data[4])
		{
      $this->log('Invalid verification request - verification code has expired', $code, Wgpwpp_Log::TYPE_ERROR);
			$response = [
				'code'  => 2003,
				'msg'   => 'Authorization header is expired!',
			];
			return NULL;
		}

		wp_set_current_user((int)$data[3], $data[2]);

		$code = $this->authorization->get_current_code();
		if (!$code)
		{
      $this->log('Invalid verification request - missing plugin verification data', NULL, Wgpwpp_Log::TYPE_ERROR);
			return [
				'code'  => 2004,
				'msg'   => 'Missing verification data!',
			];
		}

		if ($code['random'] !== $data[1])
		{
      $this->log('Invalid verification request - invalid verification code', $code, Wgpwpp_Log::TYPE_ERROR);
			return [
				'code'  => 2002,
				'msg'   => 'Invalid authorization header!',
			];
		}

		return $data;
	}


	/**
	 * Return WP site URL
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function verify_url(WP_REST_Request $request): array
	{
		$response = [];
		$state_data = $this->validate_verification_request( $request, $response);
		if (is_null($state_data))
    {
      $this->log('Validation of verification request failed', $response, Wgpwpp_Log::TYPE_ERROR);
      return $response;
    }

    $this->log('Validation of verification request successful');

		$this->set_verification_transient(['url_verified' => true]);

		return [
			'code'  => 1000,
			'data'  => [
				'url'   => get_site_url(),
			],
		];
	}


	/**
	 * Process verification request
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request request
	 * @return array
	 */
	public function process_verification_request(WP_REST_Request $request): array
	{
		$response = [];
		$state_data = $this->validate_verification_request($request, $response);
		if (is_null($state_data))
    {
      $this->log('Verification request validation failed', NULL, Wgpwpp_Log::TYPE_ERROR);
      return $response;
    }

		list( , , $user_login, $user_id, ) = $state_data;

		wp_set_current_user((int)$user_id, $user_login);

		if (!$this->is_url_verified())
		{
      $this->log('Verification failed - web URL was not verified', NULL, Wgpwpp_Log::TYPE_ERROR);
			return [
				'code'  => 2003,
				'msg'   => 'Missing verification data!',
			];
		}

		$code = $this->authorization->get_current_code();
		if (!$code)
		{
      $this->log('Verification failed - missing verification data', NULL, Wgpwpp_Log::TYPE_ERROR);
			return [
				'code'  => 2003,
				'msg'   => 'Missing verification data!',
			];
		}

		if (!$this->send_verification_email($code['verifier']))
		{
      $this->log('Verification failed - failed to send email with verification code', NULL, Wgpwpp_Log::TYPE_ERROR);
			return [
				'code'  => 2004,
				'msg'   => 'Failed to send verification email!',
        'data'  => [
          'challenge' => $code['challenge'],
        ],
			];
		}

    $this->log('Verification email sent.');

		return [
			'code'  => 1000,
			'data'  => [
				'challenge' => $code['challenge'],
			],
		];
	}


	/**
	 * Sends email with authorization secret to current user
	 *
	 * @since 1.0.0
	 * @param string $secret secret
	 * @return bool
	 */
	private function send_verification_email(string $secret): bool
	{
    $email = $this->plugin->get_admin_email();

    $subject = __('Complete the registration verification of the WEDOS Global plugin', 'wgpwpp');

    $body = __('Dear WordPress administrator,', 'wgpwpp');
    $body .= "\n\n".__('We would like to inform you that a short time ago you requested the pairing of the WEDOS Global WordPress Plugin with your WEDOS customer account.', 'wgpwpp');
    $body .= "\n\n".__('In order to successfully pair the plugin and your customer account, you must enter the following verification code:', 'wgpwpp');
    $body .= "\n\n".str_pad('', strlen($secret), '-');
    $body .= "\n".$secret;
    $body .= "\n".str_pad('', strlen($secret), '-');
    $body .= "\n\n".__('After entering the code, you will be able to complete the registration process and pair the plugin and your customer account.', 'wgpwpp');
    $body .= "\n\n".__('WEDOS Internet, a.s.', 'wgpwpp');

		return wp_mail($email, $subject, $body);
	}


	/**
	 * Returns name of transient for storing authorization metadata
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function get_verification_transient_key(): string
	{
		return base64_encode(sprintf('%s-verification-data', $this->plugin->get_plugin_name()));
	}


	/**
	 * Store authorization metadata
	 *
	 * @since 1.0.0
	 * @param array|NULL $data authorization metadata
	 * @return void
	 */
	public function set_verification_transient(?array $data = NULL)
	{
		if (is_null($data))
		{
			delete_transient($this->get_verification_transient_key());
			return;
		}

		$current_data = $this->get_verification_transient();
		if (is_null($current_data))
			$current_data = [];

    $this->log('Verification data set', ($data + $current_data));

		set_transient($this->get_verification_transient_key(), ($data + $current_data));
	}


	/**
	 * Returns authorization data for validation of authorization response
	 *
	 * @since 1.0.0
	 * @return array|null
	 */
	private function get_verification_transient(): ?array
	{
		$data = get_transient($this->get_verification_transient_key());
		if (is_wp_error($data))
      return NULL;

		if (!is_array($data))
			return [];

		return $data;
	}


	/**
	 * Removes all verification transients
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function clear_verification_data()
	{
    $this->log('Clear plugin`s verification data');
		$this->set_verification_transient();
	}


  /**
   * Log for verification process
   *
   * @param string $msg
   * @param WP_Error|mixed $data
   * @param string $type
   * @return void
   */
  private function log(string $msg, $data = NULL, string $type = Wgpwpp_Log::TYPE_INFO)
  {
    $msg = "\tVERIFICATION :: ".$msg;
    $this->plugin->log->write($msg, $data, $type);
  }
}