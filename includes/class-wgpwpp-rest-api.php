<?php
if (!defined('ABSPATH'))
  exit;

require_once plugin_dir_path(WGPWPP_PLUGIN_FILE).'vendor/mlocati/ip-lib/ip-lib.php';
use IPLib\Range\Subnet,
  IPLib\Factory;

/**
 * Class - class responsible for WP REST API communication
 *
 * @since      1.1.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
abstract class Wgpwpp_REST_API
{
  /**
   * URL of endpoint to retrieve allowed origin IP addresses for verification request process
   *
   * @since 1.1.0
   */
  const VERIFICATION_ORIGIN_IPS_URL = 'https://api.wedos.com/wgpwpp/allowed_ips.json';

  /**
   * URL of endpoint to verify token
   *
   * @since 1.1.0
   */
  const VERIFY_TOKEN_ENDPOINT_URL = 'https://login.wedos.com/oauth2/verify';

  /**
   * Timeout of requests targeting authorization server
   *
   * @since 1.1.0
   */
  const AUTHORIZATION_SERVER_TIMEOUT = 5;

  /**
   * Main plugin class
   *
   * @since 1.1.3
   * @var Wgpwpp
   */
  protected Wgpwpp $plugin;


  /**
   * Constructor
   *
   * @since 1.1.3
   * @param Wgpwpp $plugin
   */
  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;
  }


  /**
   * Returns array of allowed origin IP addresses
   *
   * @since 1.1.0
   * @return array|null
   */
  private function get_allowed_ip_addresses(): ?array
  {
    $response = wp_remote_get(self::VERIFICATION_ORIGIN_IPS_URL, [
      'headers'   => ['Accept: application/json'],
      'sslverify' => false,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200)
    {
      $this->log('Failed to get allowed IP address list', $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

    $ips = json_decode($response['body']);
    if (!$ips)
    {
      $this->log('Failed to get allowed IP address list', $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

    return $ips;
  }


  /**
   * Verifies if origin server IP address is aalowed
   *
   * @since 1.1.0
   * @return void
   * @throws Exception
   */
  protected function verify_origin_ip_address()
  {
    $allowed_ips = $this->get_allowed_ip_addresses();
    if (!$allowed_ips)
      throw new Exception("Failed to retrieve list of allowed origin IP addresses", 1);

    $server_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : NULL;
    if (!$server_ip)
    {
      $this->log('Failed to get origin server IP address', $_SERVER, Wgpwpp_Log::TYPE_ERROR);
      throw new Exception("Failed to get origin server IP address", 2);
    }

    $is_ip_allowed = false;
    foreach ($allowed_ips as $ip)
    {
      $range = Subnet::parseString($ip);
      if ($range && $range->contains(Factory::parseAddressString($server_ip)))
      {
        $is_ip_allowed = true;
        break;
      }

      if ($ip === $server_ip)
      {
        $is_ip_allowed = true;
        break;
      }
    }

    if (!$is_ip_allowed)
    {
      $this->log('Not allowed origin IP address. Unauthorized request.', $server_ip, Wgpwpp_Log::TYPE_ERROR);
      throw new Exception("Not allowed origin IP address: ".$server_ip, 3);
    }
  }


  /**
   * Parse authorization header from REST API request
   *
   * @since 1.1.0
   * @param WP_REST_Request $request
   * @return string|null
   */
  protected function get_authorization_header(WP_REST_Request $request): ?string
  {
    $headers = $request->get_headers();
    if (!isset($headers['authorization']) || !is_array($headers['authorization']) || !count($headers['authorization']))
    {
      $this->log('Missing authorization header. Unauthorized request.', $request, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

    $authorization = current($headers['authorization']);
    list(, $token) = explode(' ', $authorization);

    return $token;
  }


  /**
   * Verify access token on authorization server
   *
   * Returns array with token info or throws exception in case of failure
   *
   * @param string $token access token
   * @param array $scope required scopes
   * @return array
   * @throws Exception
   * @since 1.1.0
   */
  protected function verify_token(string $token, array $scope): array
  {
    // konfigurace požadavku
    $request_cfg = [
      'method'    => 'POST',
      'headers'   => ['Authorization' => 'Bearer '.$token],
      'timeout'   => self::AUTHORIZATION_SERVER_TIMEOUT,
      'sslverify' => false,
    ];

    if (!empty($scope))
      $request_cfg['scope'] = implode(' ', $scope);

    // odeslání požadavku na API
    $response = wp_remote_post(self::VERIFY_TOKEN_ENDPOINT_URL, $request_cfg);

    // chyba API volání
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200)
    {
      $this->log('Failed to verify authorization token. Unable to contact authorization server.', $response, Wgpwpp_Log::TYPE_ERROR);
      throw new Exception("Authorization server connection failure", 1);
    }

    $response_data = json_decode($response['body'], true);
    if (!$response_data || (int)$response_data['code'] !== 1000)
    {
      $msg = $response_data['msg'] ?? '';
      $msg = $response_data['data']['error_description'] ?? $msg;
      $this->log('Invalid authorization token.', $response_data, Wgpwpp_Log::TYPE_ERROR);
      throw new Exception('Invalid token. '.$msg, 2);
    }

    return $response_data['data'];
  }


  /**
   * Validates incoming REST API request
   *
   * @param WP_REST_Request $request request object
   * @param array $scopes required scopes fot token
   * @param string $error_msg error message in case o failure (reference)
   * @return bool
   */
  protected function authorize_request(WP_REST_Request $request, array $scopes, string &$error_msg = ''): bool
  {
    // verify origin IP address
    try
    {
      $this->verify_origin_ip_address();
    }
    catch (Exception $e)
    {
      $error_msg = sprintf("Failed to verify origin IP address. (%s)", $e->getMessage());
      return false;
    }

    // parse token from request header
    $token = $this->get_authorization_header($request);
    if (!$token)
    {
      $error_msg = "Missing or invalid authorization header";
      return false;
    }

    // verify token
    try
    {
      $this->verify_token($token, $scopes);
    }
    catch (Exception $e)
    {
      $error_msg = sprintf("Invalid token. (%s)", $e->getMessage());
      return false;
    }

    return true;
  }


  /**
   * Log for WP REST API
   *
   * @since 1.1.3
   * @param string $msg
   * @param WP_Error|mixed $data
   * @param string $type
   * @return void
   */
  private function log(string $msg, $data = NULL, string $type = Wgpwpp_Log::TYPE_INFO)
  {
    $msg = "\tWP REST API :: ".$msg;
    $this->plugin->log->write($msg, $data, $type);
  }
}