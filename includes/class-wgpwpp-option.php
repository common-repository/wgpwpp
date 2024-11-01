<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for plugins option
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Option
{
	const OPTION_CLIENT_ID = 'clid';
	const OPTION_CLIENT_SECRET = 'cls';
	const OPTION_CLIENT_METADATA = 'clmd';
	const OPTION_CLIENT_TOKEN = 'clt';
	const OPTION_SERVICE_NAME = 'sn';
	const OPTION_SERVICE_TLD = 'st';
	const OPTION_GLOBAL_ID = 'gid';
	const OPTION_SERVICE_ID = 'sid';
	const OPTION_SERVICE_URL = 'surl';
	const OPTION_SERVICE_TYPE = 'stype';
	const OPTION_SERVICE_STATE = 'ss';
	const OPTION_SERVICE_STATE_DATA = 'ssd';
	const OPTION_SERVICE_DATA = 'sd';
	const OPTION_SERVICE_LAST_UPDATE = 'slu';
	const OPTION_SERVICE_CACHE = 'sc';
  const OPTION_LOG = 'log';
  const OPTION_WP_CACHE_STATUS = 'wpcache';
  const OPTION_WP_CACHE_TTL = 'wpcachettl';
  const OPTION_WP_CACHE_IGNORE_COOKIES = 'wpcacheic';
  const OPTION_WP_CACHE_IGNORE_QUERY_VARS = 'wpcacheiqv';

	/**
	 * Objekt pluginu
	 *
	 * @since 1.0.0
	 * @var Wgpwpp
	 */
	private Wgpwpp $plugin;

	/**
	 * Array with plugin options
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $options;

	/**
	 * Default plugin options
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static array $default = [
		self::OPTION_CLIENT_ID                  => null,
		self::OPTION_CLIENT_SECRET              => null,
		self::OPTION_CLIENT_METADATA            => null,
		self::OPTION_CLIENT_TOKEN               => null,
		self::OPTION_SERVICE_NAME               => null,
		self::OPTION_SERVICE_TLD                => null,
		self::OPTION_GLOBAL_ID                  => null,
		self::OPTION_SERVICE_ID                 => null,
		self::OPTION_SERVICE_URL                => null,
		self::OPTION_SERVICE_TYPE               => null,
		self::OPTION_SERVICE_STATE              => null,
		self::OPTION_SERVICE_STATE_DATA         => null,
		self::OPTION_SERVICE_DATA               => null,
		self::OPTION_SERVICE_CACHE              => null,
		self::OPTION_SERVICE_LAST_UPDATE        => null,
		self::OPTION_LOG                        => null,
		self::OPTION_WP_CACHE_STATUS            => null,
		self::OPTION_WP_CACHE_TTL               => null,
		self::OPTION_WP_CACHE_IGNORE_COOKIES    => null,
		self::OPTION_WP_CACHE_IGNORE_QUERY_VARS => null,
	];


	/**
	 * Construct
	 *
	 * @param Wgpwpp $plugin plugin instance
	 *
	 * @since 1.0.0
	 */
	public function __construct( Wgpwpp $plugin )
	{
		$this->plugin  = $plugin;
		$this->options = self::_get();
	}


	/**
	 * Returns required option
	 *
	 * @param string $param option parameter name
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function get(string $param = '', $default = false)
	{
		if (!$param)
			return $this->options;

		if (!array_key_exists($param, $this->options))
			return $default;

    if (is_null($this->options[$param]))
      return $default;

		return $this->options[$param];
	}


  /**
   * Returns plugin option
   *
   * @return array
   */
  public static function _get(): array
  {
    $options = get_option(WGPWPP_PLUGIN_NAME);
    if (false === $options)
      return self::$default;

    if (!is_array($options))
      $options = [];

    return $options + self::$default;
  }


	/**
	 * Set option
	 *
	 * @since 1.0.0
	 * @param string $param option parameter name
	 * @param mixed $value option parameter value
	 * @return void
	 */
	public function set(string $param, $value)
	{
		if (!self::_set($param, $value))
			return;

    $this->options[$param] = $value;
	}


  /**
   * Set option
   *
   * @since 1.1.0
   * @param string $param option parameter name
   * @param mixed $value option parameter value
   * @return bool
   */
  public static function _set(string $param, $value, bool $network = false): bool
  {
    $options = self::_get();

    if (!array_key_exists($param, $options))
      return false;

    $options[$param] = $value;

    if (Wgpwpp::is_multisite() && ($network || Wgpwpp::is_multisite_subdirectories()))
    {
      $blog_ids = Wgpwpp::get_multisite_ids();
      foreach ($blog_ids as $blog_id)
      {
        switch_to_blog($blog_id);

        update_option(WGPWPP_PLUGIN_NAME, $options);

        restore_current_blog();
      }

      return true;
    }

    update_option(WGPWPP_PLUGIN_NAME, $options);

    return true;
  }


	/**
	 * Reset all options
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reset()
	{
    if (Wgpwpp::is_multisite() && Wgpwpp::is_multisite_subdirectories())
    {
      $blog_ids = Wgpwpp::get_multisite_ids();
      foreach ($blog_ids as $blog_id)
      {
        switch_to_blog($blog_id);

        $this->plugin->destroy_client();
        $this->plugin->delete_service();
        $this->options = self::$default;
        update_option($this->plugin->get_plugin_name(), $this->options);
        delete_option($this->plugin->get_plugin_name());

        restore_current_blog();
      }
      return;
    }

    $this->plugin->destroy_client();
    $this->plugin->delete_service();
    $this->options = self::$default;
    update_option($this->plugin->get_plugin_name(), $this->options);
		delete_option($this->plugin->get_plugin_name());
    delete_option('wgpwpp_reports');
    delete_option('wgpwpp_wp_cache');
    delete_option('wgpwpp_cdn_cache');
	}
}