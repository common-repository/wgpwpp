<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for WGP service
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Service
{
	/**
	 * URI of WGP REST API
	 *
	 * @since 1.0.0
	 */
	const WGP_API_URI = 'https://api.wedos.com/hosting/wedos-protection/';

	/**
	 * REST API method name for creating service
	 *
	 * @since 1.0.0
	 */
	const WGP_API_METHOD_CREATE = 'wgpwppCreate';

	/**
	 * REST API method name for retrieve service info
	 *
	 * @since 1.0.0
	 */
	const WGP_API_METHOD_INFO = 'wgpwppShow';

  /**
   * REST API method name for cache status control
   *
   * @since 1.1.0
   */
	const WGP_API_METHOD_CACHE = 'wgpwppCache';

  /**
   * REST API method name for retrieve OpenSearch statistics data
   *
   * @since 1.1.7
   */
	const WGP_API_METHOD_OPENSEARCH = 'wgpwppOpensearch';

  /**
   * REST API method name for retrieve OpenSearch statistics data
   *
   * @since 1.2.2
   */ 
  const WGP_API_METHOD_CACHE_PURGE = 'wgpwppCachePurge';

  /**
   * REST API method name for retrieve OpenSearch statistics data
   *
   * @since 1.2.2
   */
  const WGP_API_METHOD_RETRY_STATE = 'wgpwppRetryState';

  /**
   * Time limit for cache purge request in seconds
   *
   * @since 1.2.2
   */
  const CACHE_PURGE_LIMIT = 60;

  /**
   * OpenSearch data cache duration in hours
   *
   * @since 1.1.7
   */
  const OPENSEARCH_CACHE_HOURS = 24;

  /**
   * OpenSearch data type - count of request loaded from CDN cache
   *
   * @since 1.1.7
   */
  const OPENSEARCH_DATA_TYPE_CACHE = 'cache';


  /**
   * OpenSearch data type - count of blocked requests
   *
   * @since 1.1.7
   */
  const OPENSEARCH_DATA_TYPE_DDOS = 'ddos';


  /**
   * OpenSearch data type - count of blocked robots
   *
   * @since 1.1.7
   */
  const OPENSEARCH_DATA_TYPE_ROBOTS = 'robots';

  /**
   * Link to DNS administration
   *
   * @since 1.0.0
   */
  const DNS_ADMIN_LINK = 'https://client.wedos.com/dns/rows.html?id=/';

  /**
   * WEDOS Global administration URL
   *
   * @since 1.2.2
   */
  const WGP_URL = 'https://client.wedos.global/protection/domains/';

	const STATE_AUTOPILOT = 'autopilot';

	const STATE_STUCK = 'stuck';

	const STATE_PENDING_TXT = 'pending_txt';

	const STATE_REPLICATING = 'replicating';

	const STATE_PROCESSING = 'processing';

	const STATE_PENDING_DNS = 'pending_dns';

	const STATE_PENDING_NS = 'pending_ns';

	const STATE_PENDING_CRT = 'pending_crt';

	const STATE_ERROR_CRT = 'error_crt';

	const STATE_DISABLED = 'disabled';

	const STATE_ACTIVE = 'active';

	/**
	 * Plugin instance
	 *
	 * @since 1.0.0
	 * @var Wgpwpp
	 */
	private Wgpwpp $plugin;

	/**
	 * Service name
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $service_name;

	/**
	 * Service (domain) TLD
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $service_tld;

  /**
   * WEDOS Global ID
   *
   * @since 1.1.0
   * @var int|null
   */
  private ?int $global_id;

  /**
   * WEDOS Service ID
   *
   * @since 1.1.0
   * @var int|null
   */
  private ?int $service_id;

  /**
   * WEDOS Service Type
   *
   * @since 1.1.0
   * @var string|null
   */
  private ?string $service_type;

	/**
	 * Service state
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $service_state;

  /**
   * WDOS Global administration domain URL
   *
   * @var string|null
   * @since 1.2.2
   */
  private ?string $service_url;

	/**
	 * Service state data
	 *
	 * @since 1.0.0
	 * @var array|null
	 */
	private ?array $service_state_data;

  /**
   * Service data
   *
   * @since 1.0.0
   * @var array|null
   */
  private ?array $service_data;

	/**
	 * Unix timestamp of last service update from API
	 *
	 * @since 1.0.0
	 * @var int|null
	 */
	private ?int $service_last_update;

  /**
   * Last service info
   *
   * @since 1.0.0
   * @var array
   */
  private static array $service_info;

  /**
   * CDN Cache Status
   *
   * @since 1.1.0
   * @var bool
   */
  private bool $service_cache;


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

		$this->service_name = $this->plugin->option->get(Wgpwpp_Option::OPTION_SERVICE_NAME, null);
		$this->service_tld = $this->plugin->option->get(Wgpwpp_Option::OPTION_SERVICE_TLD, null);
		$this->global_id = $this->plugin->option->get(Wgpwpp_Option::OPTION_GLOBAL_ID, null);
		$this->service_id = $this->plugin->option->get(Wgpwpp_Option::OPTION_SERVICE_ID, null);
		$this->service_type = $this->plugin->option->get(Wgpwpp_Option::OPTION_SERVICE_TYPE, null);
		$this->service_url = $this->plugin->option->get(Wgpwpp_Option::OPTION_SERVICE_URL, null);
		$this->service_state = $this->plugin->option->get( Wgpwpp_Option::OPTION_SERVICE_STATE, null);
		$this->service_cache = $this->plugin->option->get( Wgpwpp_Option::OPTION_SERVICE_CACHE);

		$this->service_state_data = $this->plugin->option->get( Wgpwpp_Option::OPTION_SERVICE_STATE_DATA, null);
    $this->service_data = $this->plugin->option->get(Wgpwpp_Option::OPTION_SERVICE_DATA, null);

		$this->service_last_update = $this->plugin->option->get( Wgpwpp_Option::OPTION_SERVICE_LAST_UPDATE, null);
	}


	/**
	 * Checks if service has been created for this plugin
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_created(): bool
	{
		return $this->service_name && $this->service_tld && $this->service_state && !$this->is_disabled();
	}


  /**
   * Checks if domain is disabled in WGP
   *
   * @since 1.0.0
   * @return bool
   */
  public function is_disabled(): bool
  {
    return $this->service_state === self::STATE_DISABLED;
  }


	/**
	 * Checks if the service is waiting for free resources
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_stucked(): bool
	{
		return in_array($this->service_state, [self::STATE_AUTOPILOT, self::STATE_STUCK, self::STATE_PROCESSING, self::STATE_REPLICATING, self::STATE_PENDING_DNS]);
	}


	/**
	 * Checks if ownership of the domain is verified
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_verified(): bool
	{
		if (!$this->is_created())
			return false;

		return $this->get_service_state() !== self::STATE_PENDING_TXT;
	}


	/**
	 * Checks if service is waiting for NS setting at the domain
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_pending_ns(): bool
	{
		if (!$this->is_created() || !$this->is_verified())
			return false;

		return $this->service_state === self::STATE_PENDING_NS;
	}


	/**
	 * Checks if service is waiting for certificate
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_pending_crt(): bool
	{
		return $this->get_service_state() === self::STATE_PENDING_CRT;
	}


  /**
   * Checks if an error occured bz certificate generation
   *
   * @since 1.2.2
   * @return bool
   */
  public function is_error_crt(): bool
  {
    return $this->get_service_state() === self::STATE_ERROR_CRT;
  }


	/**
	 * Checks if service is active
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_active(): bool
	{
		if ($this->get_service_state() !== self::STATE_ACTIVE)
			return false;

		if (!$this->is_pointing_to_proxy())
      return false;

		return true;
	}


	/**
	 * Checks if service is waiting for DNS records approval
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_dns_approved(): bool
	{
		if ($this->get_service_state() !== self::STATE_ACTIVE)
			return true;

		$data = $this->get_service_state_data();
		if (!is_array($data) || !isset($data['dns_approval_url']))
			return true;

		return false;
	}


  /**
   * Checks if domains DNS is pointing to WGP proxy IPs
   *
   * @since 1.0.0
   * @return bool
   */
  public function is_pointing_to_proxy(): bool
  {
    if ($this->get_service_state() !== self::STATE_ACTIVE)
      return false;

    $data = $this->get_service_state_data();
    if (!isset($data['dns_pointing_to_proxy']))
      return false;

    $res = $data['dns_pointing_to_proxy'];
    if ($res === 'false' || $res == 0)
      $res = false;

    return (bool)$res;
  }


	/**
	 * Returns service name
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function get_service_name(): ?string
	{
		return $this->service_name;
	}


	/**
	 * Returns service TLD
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function get_service_tld(): ?string
	{
		return $this->service_tld;
	}


  /**
   * Returns WEDOS Service ID
   *
   * @since 1.1.0
   * @return int|null
   */
  public function get_service_id(): ?int
  {
    return $this->service_id;
  }


  /**
   * Returns WEDOS Global ID
   *
   * @since 1.1.0
   * @return int|null
   */
  public function get_global_id(): ?int
  {
    return $this->global_id;
  }


  /**
   * Returns WEDOS Global administration URL
   *
   * @return string|null
   * @since 1.2.2
   */
  public function get_service_url(): ?string
  {
    $service_url = $this->service_url;
    if ($service_url)
      return $service_url;

    if (!$this->service_id)
      return null;

    return self::WGP_URL.$this->service_id;
  }


  /**
   * Sets WEDOS Global administration URL
   *
   * @param string|null $url WEDOS Global administration URL
   * @return void
   * @since 1.2.2
   */
  public function set_service_url(?string $url)
  {
    $this->service_url = $url;
    $this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_URL, $this->service_url);
  }


  /**
   * Returns WEDOS Service Type
   *
   * @since 1.1.0
   * @return string|null
   */
  public function get_service_type(): ?string
  {
    return $this->service_type;
  }


	/**
	 * Returns service state
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function get_service_state(): ?string
	{
		return $this->service_state;
	}


	/**
	 * Returns service state data
	 *
	 * @since 1.0.0
	 * @return array|null
	 */
	public function get_service_state_data(): ?array
	{
		return $this->service_state_data;
	}


  /**
   * Returns service data
   *
   * @since 1.0.0
   * @return array|null
   */
  public function get_service_data(): ?array
  {
    return $this->service_data;
  }


  /**
   * Set service CDN Cache status
   *
   * @since 1.1.0
   * @param bool $status CDN cache status
   * @return void
   */
  private function set_service_cache_status(bool $status)
  {
    $this->service_cache = $status;
    $this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_CACHE, $status);
  }


  /**
   * Returns service CDN Cache status
   *
   * @since 1.1.0
   * @return bool
   */
  public function get_service_cache_status(): bool
  {
    return $this->service_cache;
  }


	/**
	 * Returns unix timestamp of last update from API
	 *
	 * @since 1.0.0
	 * @return int|null
	 */
	public function get_service_last_update(bool $diff = false): ?int
	{
		return $this->service_last_update;
	}


	/**
	 * Set service name
	 *
	 * @since 1.0.0
	 * @param string|NULL $service_name
	 * @return void
	 */
	private function set_service_name(?string $service_name)
	{
		$this->service_name = $service_name;
		$this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_NAME, $service_name);
	}


	/**
	 * Set service TLD
	 *
	 * @since 1.0.0
	 * @param string|NULL $service_tld service TLD
	 * @return void
	 */
	private function set_service_tld(?string $service_tld)
	{
		$this->service_tld = $service_tld;
		$this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_TLD, $service_tld);
	}


  /**
   * Set WEDOS Global ID
   *
   * @since 1.1.0
   * @param int|null $global_id
   * @return void
   */
  private function set_global_id(?int $global_id)
  {
    $this->global_id = $global_id;
    $this->plugin->option->set(Wgpwpp_Option::OPTION_GLOBAL_ID, $global_id);
  }


  /**
   * Set WEDOS Service ID
   *
   * @since 1.1.0
   * @param int|null $service_id
   * @return void
   */
  private function set_service_id(?int $service_id)
  {
    $this->service_id = $service_id;
    $this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_ID, $service_id);
  }


  /**
   * Set WEDOS Service Type
   *
   * @since 1.1.0
   * @param string|null $service_type
   * @return void
   */
  private function set_service_type(?string $service_type)
  {
    $this->service_type = $service_type;
    $this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_TYPE, $service_type);
  }


	/**
	 * Set service state
	 *
	 * @since 1.0.0
	 * @param string|NULL $service_state service state
	 * @return void
	 */
	private function set_service_state(?string $service_state)
	{
		$this->service_state = $service_state;
		$this->plugin->option->set( Wgpwpp_Option::OPTION_SERVICE_STATE, $service_state);
	}


	/**
	 * Set service state data
	 *
	 * @since 1.0.0
	 * @param array|NULL $service_state_data state data
	 * @return void
	 */
	private function set_service_state_data(?array $service_state_data)
	{
		$this->service_state_data = $service_state_data;
		$this->plugin->option->set( Wgpwpp_Option::OPTION_SERVICE_STATE_DATA, $service_state_data);
	}


  /**
   * Set service data
   *
   * @since 1.0.0
   * @param array|null $service_data
   * @return void
   */
  private function set_service_data(?array $service_data)
  {
    $this->service_data = $service_data;
    $this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_DATA, $service_data);
  }


	/**
	 * Set unix timestamp of last update from API
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function set_service_last_update(?int $timestamp = 0)
	{
		if ($timestamp === 0)
			$timestamp = time();

		$this->service_last_update = $timestamp;
		$this->plugin->option->set(Wgpwpp_Option::OPTION_SERVICE_LAST_UPDATE, $this->service_last_update);
	}


	/**
	 * Creates domain in WGP
	 *
	 * Error codes:
	 * - 1 = failed to get WP host name
	 * - 2 = API error
	 * - 3 = invalid domain name
	 * - 4 = domain already exists
	 * - 5 = unknown error
	 *
	 * @since 1.0.0
	 * @return mixed|true|WP_Error
	 */
	public function create()
	{
		if ($this->is_created())
			return true;

		$create = $this->create_request();
		if (is_wp_error($create) && $create->get_error_code() !== 4)
    {
      $this->log('CREATE :: Failed to create WGP service', $create, Wgpwpp_Log::TYPE_ERROR);
      return $create;
    }

		return $this->info();
	}


	/**
	 * Creates domain in WGP
	 *
	 * Error codes:
	 * - 1 = failed to get WP host name
	 * - 2 = API error
	 * - 3 = invalid domain name
	 * - 4 = domain not found in WGP
	 * - 5 = unknown error
	 *
	 * @since 1.0.0
	 * @return mixed|true|WP_Error
	 */
	public function info()
	{
		$info = $this->info_request();
		if (is_wp_error($info))
    {
      $this->log('INFO :: Failed to load WGP service info', $info, Wgpwpp_Log::TYPE_ERROR);
      return $info;
    }

		return true;
	}


	/**
	 * API request - get info about domain in WGP
	 *
	 * Error codes:
	 * - 1 = failed to get WP host name
	 * - 2 = API error
	 * - 3 = invalid domain name
	 * - 4 = domain not found in WGP
	 * - 5 = unsupported subdomains
	 * - 6 = unsupported TLD
	 * - 10 = unknown error
	 *
	 * @since 1.0.0
	 * @return mixed|WP_Error
	 */
	private function info_request()
	{
		$host = $this->plugin->get_host();
		if (is_wp_error($host))
			return new WP_Error(1, $host->get_error_message());

    if (empty(self::$service_info))
    {
      $res = $this->request(self::WGP_API_METHOD_INFO, ['domain' => $host]);

      if (is_null($res))
        return new WP_Error(2, __('An error occurred while connecting to WGP API', 'wgpwpp'));

      if (!$res['success']) {
        switch ($res['code']) {
          case 5000:
            return new WP_Error(3, __('Invalid domain name', 'wgpwpp'));

          case 5001:
            $this->delete();;
            return new WP_Error(4, __('Domain not found', 'wgpwpp'));

          case 5002:
            return new WP_Error(5, __('Subdomains are not supported. Only second level domain names are supported.', 'wgpwpp'));
          case 5003:
            return new WP_Error(6, __('Unsupported TLD', 'wgpwpp'));

          default:
            return new WP_Error(10, __('Unknown internal error', 'wgpwpp'));
        }
      }

      self::$service_info = $res;
    }

    $info = self::$service_info['data'];

    if ($this->service_data_changed($info))
    {
      $global_id = $info['domain_id'] ?? null;
      $service_id = $info['wedos_service_id'] ?? null;
      $service_type = $info['wedos_service_type'] ?? null;
      $service_url = $info['url'] ?? null;

      $this->set_service_name($info['domain']);
      $this->set_service_tld($info['domain_tld']);
      $this->set_global_id($global_id);
      $this->set_service_id($service_id);
      $this->set_service_type($service_type);
      $this->set_service_url($service_url);
      $this->set_service_state($info['state']);

      // CDN Cache setting
      $service_settings = $info['settings'] ?? [];
      $service_cache = (bool)($service_settings['cache'] ?? false);
      $this->set_service_cache_status($service_cache);

      // Service state data
      if (isset($info['state_data']) && is_array($info['state_data']))
        $this->set_service_state_data($info['state_data']);
      else
        $this->set_service_state_data(null);

      // Service data
      if (isset($info['service_data']) && is_array($info['service_data']))
        $this->set_service_data($info['service_data']);
      else
        $this->set_service_data(null);

      $this->log('Service data changed', self::$service_info);
    }

		$this->set_service_last_update();

		return $info;
	}


  /**
   * Enables or disables CDN Cache
   *
   * @since 1.1.0
   * @param bool $cache_status required cache status
   * @return bool
   */
  public function cache_control(bool $cache_status): bool
  {
    $res = $this->cache_request($cache_status);
    if (is_wp_error($res))
    {
      $this->log('Failed to change CDN cache status', $res);
      return false;
    }

    $this->log('CDN Cache '.($cache_status ? 'enabled' : 'disabled'));
    return true;
  }


  /**
   * Sends request to enable or disable CDN Cache
   *
   * @param bool $cache_status required cache status
   * @return bool|WP_Error
   */
  private function cache_request(bool $cache_status)
  {
    if (!$this->get_service_id() || !$this->get_service_type())
      return new WP_Error(1, 'Service is not correctly installed');

    $res = $this->request(self::WGP_API_METHOD_CACHE, [
      'service_id'    => $this->get_service_id(),
      'service_type'  => $this->get_service_type(),
      'cache'         => (int)$cache_status,
    ]);

    if (is_null($res))
      return new WP_Error(2, __('An error occurred while connecting to WGP API', 'wgpwpp'));

    if (!$res['success']) {
      switch ($res['code']) {
        case 5001:
          return new WP_Error(3, __('Validation failed', 'wgpwpp'));

        case 5002:
          $this->delete();;
          return new WP_Error(4, __('Domain not found', 'wgpwpp'));

        case 5003:
          return $cache_status;

        default:
          return new WP_Error(10, __('Unknown internal error', 'wgpwpp'));
      }
    }

    return $cache_status;
  }


  /**
   * Checks if service data has changed
   *
   * @since 1.0.0
   * @param array $service_info service info from WGP API
   * @return bool
   */
  private function service_data_changed(array $service_info): bool
  {
    $service_info += [
      'domain'              => null,
      'domain_tld'          => null,
      'state'               => null,
      'state_data'          => null,
      'domain_id'           => null,
      'wedos_service_id'    => null,
      'wedos_service_type'  => null,
      'service_data'        => null,
      'url'                 => null,
    ];

    // CDN Cache setting
    $service_settings = $service_info['settings'] ?? [];
    $service_cache = (bool)($service_settings['cache'] ?? false);

    if ($this->get_service_name() !== $service_info['domain'])
      return true;

    if ($this->get_service_tld() !== $service_info['domain_tld'])
      return true;

    if ($this->get_global_id() !== $service_info['domain_id'])
      return true;

    if ($this->get_service_id() !== $service_info['wedos_service_id'])
      return true;

    if ($this->get_service_type() !== $service_info['wedos_service_type'])
      return true;

    if ($this->get_service_url() !== $service_info['url'])
      return true;

    if ($this->get_service_cache_status() !== $service_cache)
      return true;

    if ($this->get_service_state() !== $service_info['state'])
      return true;

    if ($this->get_service_state_data() !== $service_info['state_data'])
      return true;

    // unset ignored items
    $service_data_current = $this->get_service_data();
    unset($service_data_current['upgrade_link']);
    $service_data_new = $service_info['service_data'];
    unset($service_data_new['upgrade_link']);

    if ($service_data_current !== $service_data_new)
      return true;

    return false;
  }


	/**
	 * Remove service
	 *
	 * @since 1.0.0
	 */
	public function delete()
	{
		$this->set_service_name(NULL);
		$this->set_service_tld(NULL);
		$this->set_global_id(NULL);
		$this->set_service_id(NULL);
		$this->set_service_type(NULL);
		$this->set_service_url(NULL);
		$this->set_service_cache_status(false);
		$this->set_service_state(NULL);
		$this->set_service_state_data(NULL);
		$this->set_service_last_update(NULL);
	}


	/**
	 * API request - creates domain in WGP
	 *
	 * Error codes:
	 * - 1 = failed to get WP host name
	 * - 2 = API error
	 * - 3 = invalid domain name
	 * - 4 = domain already exists
   * - 5 = unsupported subdomains
   * - 6 = unsupported TLD
	 * - 10 = unknown error
	 *
	 * @since 1.0.0
	 * @return mixed|WP_Error
	 */
	private function create_request()
	{
		$host = $this->plugin->get_host();
		if (is_wp_error($host))
			return new WP_Error(1, $host->get_error_message());

		$res = $this->request(self::WGP_API_METHOD_CREATE, ['domain' => $host]);

		if (is_null($res))
			return new WP_Error(2, __('An error occurred while connecting to WGP API. Please try again later.', 'wgpwpp'));

		if (!$res['success'])
		{
			switch ($res['code'])
			{
				case 5000: return new WP_Error(3, __('Invalid domain name format', 'wgpwpp'));
				case 5001: return new WP_Error(4, __('Domain already exists for this account', 'wgpwpp'));
        case 5002: return new WP_Error(5, __('Subdomains are not supported. Only second level domain names are supported.', 'wgpwpp'));
        case 5003: return new WP_Error(6, __('Unsupported TLD', 'wgpwpp'));
				default: return new WP_Error(10, __('Unknown internal error', 'wgpwpp'));
			}
		}

		return $res['data'];
	}


  /**
   * Retry TLS certificate generation
   *
   * @return bool
   * @since 1.2.2
   */
  public function retry_state()
  {
    $retry = $this->retry_state_request();

    if (is_wp_error($retry))
    {
      $this->log('RETRY STATE :: Failed to retry TLS certificate generation', $retry, Wgpwpp_Log::TYPE_ERROR);
      return $retry;
    }

    $this->log('RETRY STATE :: Success');

    $this->info();

    return true;
  }


  /**
   * API request - retry TLS certificate generation
   *
   * Error codes:
   * - 1 = invalid service status
   * - 2 = service is not properly installed
   * - 3 = API error
   * - 4 = invalid domain name
   * - 5 = service not found
   * - 10 = unknown error
   *
   * @since 1.2.2
   * @return mixed|WP_Error
   */
  private function retry_state_request()
  {
    if ($this->get_service_state() !== self::STATE_ERROR_CRT)
      return new WP_Error(1, __('Invalid service status!', 'wgpwpp'), $this->service_state);

    $domain = $this->service_name;
    if (!$domain)
      return new WP_Error(2, __('The service is not properly installed!', 'wgpwpp'));

    $res = $this->request(self::WGP_API_METHOD_RETRY_STATE, ['domain' => $domain]);

    if (is_null($res))
      return new WP_Error(3, __('An error occurred while connecting to WGP API. Please try again later.', 'wgpwpp'));

    if (!$res['success'])
    {
      switch ($res['code'])
      {
        case 2000: return new WP_Error(4, __('Invalid domain name format!', 'wgpwpp'));
        case 5000: return new WP_Error(5, __('The service was not found!', 'wgpwpp'));
        default: return new WP_Error(10, __('Unknown internal error', 'wgpwpp'));
      }
    }

    return $res['data'];
  }


  /**
	 * CDN Cache purge
	 *
   * @since 1.2.2
   * @param string $error error message (reference)
   * @return bool
	 */
  public function cache_purge(string &$error = ''): bool
  {
    $result = $this->cache_purge_request();

    if (is_wp_error($result))
    {
      $error = $result->get_error_message();
      $this->log('Failed to purge CDN cache', $result, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    $this->log('CDN cache purged', $result, Wgpwpp_Log::TYPE_ERROR);

    return true;
  }


  /**
	 * Calls CDN cache purge request
	 *
   * @since 1.2.2
   * @return WP_Error|boolean
	 */
  private function cache_purge_request()
  {
    if (!$this->get_service_id() || !$this->get_service_type())
      return new WP_Error(1, __('Service is not correctly installed', 'wgpwpp'));

    $res = $this->request(self::WGP_API_METHOD_CACHE_PURGE, [
      'service_id'    => $this->get_service_id(),
      'service_type'  => $this->get_service_type(),
      'limiter_decay' => self::CACHE_PURGE_LIMIT,
    ]);

    if (is_null($res))
      return new WP_Error(2, __('An error occurred while connecting to WGP API. Please try again later.', 'wgpwpp'));

    if (!$res['success'])
    {
      switch ($res['code'])
      {
        case 6000: // System error
          return new WP_Error(3, __('System error. Please try again later.', 'wgpwpp'));

        case 5010:
          $time = $res['data']['limiter_available_in'] ?? 10;
          $message = sprintf(__('CDN cache purge request can be called every %d seconds.', 'wgpwpp'), self::CACHE_PURGE_LIMIT);
          $message .= ' '.sprintf(_n('You can repeat request in %s second.', 'You can repeat request in %s seconds.', $time, 'wgpwpp'), number_format_i18n($time));
          return new WP_Error(4, $message);

        case 5001:
          return new WP_Error(1, __('Service is not correctly installed', 'wgpwpp'));

        default:
          return new WP_Error(10, __('Unknown internal error', 'wgpwpp'));
      }
    }

    return true;
  }


  /**
   * Returns OpenSearch statistics data
   *
   * Returns array with requested data or null in case of failure
   *
   * Result array format:
   *  - domain     = requested domain
   *  - type       = requested data type
   *  - values     = requested data array (SQL date => requests count)
   *  - timestamp  = timestamp of resulted data
   *
   * @param string $type type of requested data (self::OPENSEARCH_DATA_TYPE_*)
   * @param bool $no_cache disable transient cache
   * @return array|null
   *@since 1.1.7
   */
  public function get_opensearch_data(string $type, bool $no_cache = false): ?array
  {
    $cache_key = self::WGP_API_METHOD_OPENSEARCH.'_'.$type;

    if ($no_cache)
    {
      delete_transient($cache_key);
      return $this->get_opensearch_data_request($type);
    }

    $res = get_transient($cache_key);
    if ($res !== false)
      return $res;

    $res = $this->get_opensearch_data_request($type);

    if (!is_null($res))
      set_transient($cache_key, $res, self::OPENSEARCH_CACHE_HOURS * HOUR_IN_SECONDS);

    return $res;
  }


  /**
   * Requests OpenSearch statistics data from REST API
   *
   * Returns array with requested data or null in case of failure
   *
   * Result array format:
   * - domain     = requested domain
   * - type       = requested data type
   * - values     = requested data array (SQL date => requests count)
   * - timestamp  = timestamp of resulted data
   *
   * @since 1.1.7
   * @param string $type type of requested data (self::OPENSEARCH_DATA_TYPE_*)
   * @return array|null
   */
  private function get_opensearch_data_request(string $type): ?array
  {
    $method_uri = self::WGP_API_METHOD_OPENSEARCH.'/'.$this->get_service_name().'/'.$type;

    $res = $this->request($method_uri, [], 'GET');

    if (is_null($res) || !$res['success'] || !is_array($res['data']))
      return null;

    $res['data']['timestamp'] = time();

    return $res['data'];
  }


	/**
	 * Calls WGP API endpoint
	 *
	 * Returns:
	 * - NULL       = WGP API error
	 * - array      = WGP API response data
	 *
	 * @param string $method API endpoint method name
	 * @param array $data request data
	 * @param string $http_method API endpoint HTTP method
   * @param bool $force_new_token force issuing new token
	 * @return mixed|NULL
	 */
	private function request(string $method, array $data = [], string $http_method = 'POST', bool $force_new_token = false)
	{
		$token = $this->plugin->get_client()->get_token($force_new_token);
		if (is_wp_error($token))
			return NULL;

		// konfigurace požadavku
		$req_cfg = [
			'headers'   => ['Authorization' => 'Bearer '.$token],
			'timeout'   => 30, // TODO
			'sslverify' => false, // TODO
			'body'      => $data,
		];

		// odeslání požadavku na API
    switch ($http_method)
    {
      case 'GET': $response = wp_remote_get(self::WGP_API_URI.$method, $req_cfg); break;
      default: $response = wp_remote_post(self::WGP_API_URI.$method, $req_cfg);
    }

		// chyba API volání
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200)
    {
      $this->log('Invalid response from WGP API', $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		$response_data = json_decode($response['body'], true);
		if (!$response_data)
    {
      $this->log('Invalid WGP API response data', $response, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		if ((int)$response_data['code'] != 1000)
    {
      $this->log('WGP API error', $response_data, Wgpwpp_Log::TYPE_ERROR);

      if ($response_data['code'] == 3002 && $response_data['msg'] === 'Invalid token')
      {
        $this->log('Trying to obtain new access token');
        return $this->request($method, $data, 'POST', true);
      }

      return NULL;
    }
    elseif ($force_new_token)
    {
      $this->log('New access token obtained');
    }

		if (!isset($response_data['data']))
    {
      $this->log('Invalid WGP API response data', $response_data, Wgpwpp_Log::TYPE_ERROR);
      return NULL;
    }

		return $response_data['data'];
	}


  /**
   * Log for service
   *
   * @param string $msg
   * @param WP_Error|mixed $data
   * @param string $type
   * @return void
   */
  private function log(string $msg, $data = NULL, string $type = Wgpwpp_Log::TYPE_INFO)
  {
    $msg = "\tSERVICE :: ".$msg;
    $this->plugin->log->write($msg, $data, $type);
  }
}
