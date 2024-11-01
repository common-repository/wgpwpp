<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for the client
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Client
{
	const REGISTRATION_ENDPOINT_URL = 'https://login.wedos.com/oauth2/register';

  const CONFIGURATION_ENDPOINT_URL = 'https://login.wedos.com/oauth2/config';

	const GRANT_TYPE = 'client_credentials';

	const RESOURCE_SCOPE = 'wgp';

	const CONFIGURATION_SCOPE_GET = 'dcrm_get';

	const CONFIGURATION_SCOPE_PUT = 'dcrm_put';

	const CONFIGURATION_SCOPE_PUT_LANG = 'dcrm_put_lang';

	const CONFIGURATION_SCOPE_PUT_SOFTWARE_VERSION = 'dcrm_put_software_version';

	const CONFIGURATION_SCOPE_PUT_CLIENT_URI = 'dcrm_put_client_uri';

	const CONFIGURATION_SCOPE_DELETE = 'dcrm_delete';

	const CONTACTS = 'hosting@wedos.com https://client.wedos.com/contact/cform.html?nologin=1';

	const SW_ID = 'wgp-wp-plugin';

	/**
	 * Plugin instance
	 *
	 * @var Wgpwpp
	 * @since 1.0.0
	 */
	private Wgpwpp $plugin;

	/**
	 * Client ID
	 *
	 * @var string|NULL
	 * @since 1.0.0
	 */
	private ?string $client_id;

	/**
	 * Client Secret
	 *
	 * @var string|NULL
	 * @since 1.0.0
	 */
	private ?string $client_secret;

	/**
	 * Client metadata
	 *
	 * @var array|NULL
	 * @since 1.0.0
	 */
	private ?array $client_metadata;

	/**
	 * Client`s token metadata
	 *
	 * @var array|NULL
	 * @since 1.0.0
	 */
	private ?array $token_metadata;

	/**
	 * Last response from authorization server
	 *
	 * @var array|NULL
	 * @since 1.0.0
	 */
	private ?array $last_response;


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param Wgpwpp $plugin plugin instance
	 * @return void
	 */
	public function __construct(Wgpwpp $plugin)
	{
		$this->plugin = $plugin;

		$this->client_id = $this->plugin->option->get(Wgpwpp_Option::OPTION_CLIENT_ID, NULL);
		$this->client_secret = $this->plugin->option->get(Wgpwpp_Option::OPTION_CLIENT_SECRET, NULL);
		$this->client_metadata = $this->plugin->option->get(Wgpwpp_Option::OPTION_CLIENT_METADATA, NULL);
		$this->token_metadata = $this->plugin->option->get(Wgpwpp_Option::OPTION_CLIENT_TOKEN, NULL);
	}


	/**
	 * Returns client id
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function get_client_id(): ?string
	{
		return $this->client_id;
	}


	/**
	 * Returns client secret
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function get_client_secret(): ?string
	{
		return $this->client_secret;
	}


	/**
	 * Returns client metadata
	 *
	 * @since 1.0.0
	 * @return array|null
	 */
	public function get_client_metadata(): ?array
	{
		return $this->client_metadata;
	}


	/**
	 * Checks if client is registered
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_registered(): bool
	{
		return !empty($this->client_id) && !empty($this->client_secret) && !empty($this->client_metadata);
	}


	/**
	 * Returns access token for API authorization
	 *
	 * @since 1.0.0
   * @param bool $force_server_request force server request to gain new access token
	 * @return string|WP_Error
	 */
	public function get_token(bool $force_server_request = false)
	{
		// client is not properly registered and configured
		if (!$this->is_registered())
			return new WP_Error(1, __('Client is not properly registered!', 'wgpwpp'));

		// there is valid token in metadata
    if (!$force_server_request)
    {
      $token = $this->get_token_from_metadata();
      if ($token)
        return $token;
    }

		// request new token from server
		$token = $this->get_token_from_server();
		if (!$token)
			return new WP_Error(2, __('Failed to gain access token from authorization server', 'wgpwpp'));

		return $token;
	}


	/**
	 * Store token metadata for reusing token until is valid
	 *
	 * @since 1.0.0
	 * @param array $token_metadata token metadata
	 * @return void
	 */
	private function set_token_metadata(array $token_metadata)
	{
		$token_metadata['expires_at'] = time() + $token_metadata['expires_in'];
		$this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_TOKEN, $token_metadata);
		$this->token_metadata = $token_metadata;
	}


	/**
	 * Requests token from authorization server
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	private function get_token_from_server(): ?string
	{
		$request_data = [
			'grant_type'    => self::GRANT_TYPE,
			'scope'         => self::RESOURCE_SCOPE,
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
		];

		// konfigurace požadavku
		$req_cfg = [
			'method'    => 'POST',
			'body'      => $request_data,
			'timeout'   => Wgpwpp_Authorization::AUTHORIZATION_SERVER_TIMEOUT,
			'sslverify' => false,
		];

		// odeslání požadavku na API
		$response = wp_remote_post(Wgpwpp_Authorization::TOKEN_ENDPOINT, $req_cfg);

		// chyba API volání
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200)
			return NULL;

		$response_data = json_decode($response['body'], true);
		if (!$response_data)
			return NULL;

		$this->last_response = $response_data;

		if ((int)$response_data['code'] !== 1000)
			return NULL;

		$this->set_token_metadata($response_data['data']);

		return $response_data['data']['access_token'];
	}


	/**
	 * Returns access token from token metadata
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	private function get_token_from_metadata(): ?string
	{
		if (!$this->token_metadata)
			return NULL;

		if ($this->token_metadata['expires_at'] - 30 < time())
			return NULL;

		return $this->token_metadata['access_token'];
	}


	/**
	 * Returns metadata for client registration
	 *
	 * @since 1.0.0
	 * @param string $locale user`s locale
	 * @return array|WP_error
	 */
	private function get_client_registration_metadata(string $locale)
	{
		$host = $this->plugin->get_host();
		if (is_wp_error($host))
			return $host;

    $required_scopes = [
      self::RESOURCE_SCOPE,
      self::CONFIGURATION_SCOPE_GET,
      self::CONFIGURATION_SCOPE_PUT,
      self::CONFIGURATION_SCOPE_PUT_LANG,
      self::CONFIGURATION_SCOPE_PUT_SOFTWARE_VERSION,
      self::CONFIGURATION_SCOPE_PUT_CLIENT_URI,
      self::CONFIGURATION_SCOPE_DELETE,
    ];

		return [
			'client_name'                   => sprintf('WEDOS Global WordPress Plugin for domain %s', $this->plugin->get_host()),
			'client_name#'.$locale          => sprintf(__('WEDOS Global WordPress Plugin for domain %s', 'wgpwpp'), $this->plugin->get_host()),
			'client_description'            => 'OAuth2 client used by WGP WordPress Plugin for activation of WEDOS Global service and its maintanance.',
			'client_description#'.$locale   => __('OAuth2 client used by WGP WordPress Plugin for activation of WEDOS Global service and its maintanance.', 'wgpwpp'),
			'client_uri'                    => get_rest_url(),
			'grant_types'                   => self::GRANT_TYPE,
			'scope'                         => implode(' ', $required_scopes),
			'contacts'                      => self::CONTACTS,
			'software_id'                   => self::SW_ID,
			'software_version'              => $this->plugin->get_version(),
		];
	}


	/**
	 * Dynamic registration of the client
	 *
	 * @since 1.0.0
	 * @param string $token token
	 * @param string $locale locale
	 * @return Wgpwpp_Client|null
	 */
	public function register(string $token, string $locale): ?Wgpwpp_Client
	{
    $this->log('CLIENT REGISTRATION');

		$metadata = $this->get_client_registration_metadata($locale);
		if (is_wp_error($metadata))
    {
      $this->log('Failed to get client registration metadata', $metadata, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		// konfigurace požadavku
		$req_cfg = [
			'method'    => 'POST',
			'headers'   => ['Authorization' => 'Bearer '.$token],
			'timeout'   => Wgpwpp_Authorization::AUTHORIZATION_SERVER_TIMEOUT,
			'sslverify' => false,
			'body'      => $metadata,
		];

    $this->log('Requesting OAuth2 client registration', $metadata);

		// odeslání požadavku na API
		$response = wp_remote_post(self::REGISTRATION_ENDPOINT_URL, $req_cfg);

		// chyba API volání
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200)
    {
      $this->log("Invalid response from authorization server", $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		$response_data = json_decode($response['body'], true);
		if (!$response_data || (int)$response_data['code'] !== 1000)
    {
      $this->log("Invalid response from authorization server", $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		$metadata = $response_data['data'];

		$this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_ID, $metadata['client_id']);
		$this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_SECRET, $metadata['client_secret']);
		$this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_METADATA, $metadata);

    $this->log('CLIENT SUCCESSFULLY REGISTERED');

		return new self($this->plugin);
	}


  /**
   * Destroys client registration
   *
   * @since 1.0.0
   * @return bool
   */
  public function destroy()
  {
    $this->log('CLIENT DESTROY');

    $registration_data = $this->get_client_metadata();
    if (is_null($registration_data))
    {
      $this->log('Unable to destroy client. Missing client metadata.', null, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $token = $registration_data['registration_access_token'] ?? null;
    if (!$token)
    {
      $this->log('Unable to destroy client. Missing configuration access token', null, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $url = self::CONFIGURATION_ENDPOINT_URL.'/'.$this->get_client_id();

    $res = wp_remote_request($url, [
      'method'    => 'DELETE',
      'headers'   => ['Authorization' => 'Bearer '.$token],
      'timeout'   => Wgpwpp_Authorization::AUTHORIZATION_SERVER_TIMEOUT,
      'sslverify' => false,
    ]);

    if (is_wp_error($res))
    {
      $this->log('Failed to destroy client.', $res, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $response_data = json_decode($res['body'], true);
    if (!$response_data || (int)$response_data['code'] !== 1000)
    {
      $this->log("Failed to destroy client. Invalid response from authorization server", $res, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_TOKEN, null);
    $this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_SECRET, null);
    $this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_METADATA, null);

    $this->log('CLIENT SUCCESSFULLY DESTROYED');

    return true;
  }


  /**
   * Updates client registration data
   *
   * @since 1.0.0
   * @param string $locale
   * @return bool
   */
  public function update(string $locale): bool
  {
    $this->log('CLIENT UPDATE');

    $metadata = $this->get_client_registration_metadata($locale);
    if (is_wp_error($metadata))
    {
      $this->log('Unable to update client. Failed to get client registration metadata.', $metadata, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $registration_data = $this->get_client_metadata();
    if (is_null($registration_data))
    {
      $this->log('Unable to update client. Missing client metadata.', null, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $token = $registration_data['registration_access_token'] ?? null;
    if (!$token)
    {
      $this->log('Unable to update client. Missing configuration access token', null, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $url = self::CONFIGURATION_ENDPOINT_URL.'/'.$this->get_client_id();

    $res = wp_remote_request($url, [
      'method'    => 'PUT',
      'headers'   => ['Authorization' => 'Bearer '.$token],
      'timeout'   => Wgpwpp_Authorization::AUTHORIZATION_SERVER_TIMEOUT,
      'sslverify' => false,
      'body'      => $metadata,
    ]);

    if (is_wp_error($res))
    {
      $this->log('Failed to update client.', $res, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $response_data = json_decode($res['body'], true);
    if (!$response_data || (int)$response_data['code'] !== 1000)
    {
      $this->log("Failed to update client. Invalid response from authorization server", $res, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $metadata = $response_data['data'];

    $this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_SECRET, $metadata['client_secret']);
    $this->plugin->option->set(Wgpwpp_Option::OPTION_CLIENT_METADATA, $metadata);

    $this->log('CLIENT SUCCESSFULLY UPDATED');

    return true;
  }


  /**
   * Log for client
   *
   * @param string $msg
   * @param WP_Error|mixed $data
   * @param string $type
   * @return void
   */
  private function log(string $msg, $data = NULL, string $type = Wgpwpp_Log::TYPE_INFO)
  {
    $msg = "\tCLIENT :: ".$msg;
    $this->plugin->log->write($msg, $data, $type);
  }
}