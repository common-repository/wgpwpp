<?php

include_once __DIR__.'/wpcache/common.php';

use function Wgpwpp_Cache\config;
use const Wgpwpp_Cache\CACHE_DIR;

if (!defined('ABSPATH'))
  exit;


/**
 * Class - class responsible for WP page caching
 *
 * Based on work https://github.com/kovshenin/surge
 *
 * @since      1.1.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_WP_Cache
{
  const LIB_DIR = __DIR__ . '/wpcache';


  /**
   * Plugin instance
   *
   * @since 1.1.0
   * @var Wgpwpp
   */
  private Wgpwpp $plugin;

  /**
   * Flags
   *
   * @since 1.1.0
   * @var array
   */
  private static array $flags;

  /**
   * Flags expire
   *
   * @since 1.1.0
   * @var array
   */
  private static array $flags_expire;

  /**
   * Known cache plugins slugs
   *
   * @since 1.1.2
   * @var array|string[]
   */
  private static array $known_cache_plugins_slugs = [
    'tenweb-speed-optimizer/tenweb_speed_optimizer.php',
    'autoptimize/autoptimize.php',
    'borlabs-cache/borlabs-cache.php',
    'breeze/breeze.php',
    'cache-enabler/cache-enabler.php',
    'comet-cache/comet-cache.php',
    'hummingbird-performance/wp-hummingbird.php',
    'hyper-cache/plugin.php',
    'litespeed-cache/litespeed-cache.php',
    'nitropack/main.php',
    'rapid-cache/rapid-cache.php',
    'sg-cachepress/sg-cachepress.php',
    'w3-total-cache/w3-total-cache.php',
    'wp-fastest-cache/wpFastestCache.php',
    'wp-super-cache/wp-cache.php',
    'wp-optimize/wp-optimize.php',
  ];


  /**
   * Constructor
   *
   * @param Wgpwpp $plugin
   * @since 1.1.0
   */
  public function __construct(Wgpwpp $plugin)
  {
    $this->plugin = $plugin;
    $this->define_hooks();
  }


  /**
   * Checks if page caching is active
   *
   * @return bool
   * @since 1.1.0
   */
  public function is_active()
  {
    return 1 === $this->plugin->option->get(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, null);
  }


  /**
   * Returns page caching status
   *
   * @return array|false|mixed|null[]|null
   * @since 1.1.0
   */
  public function get_status()
  {
    return $this->plugin->option->get(Wgpwpp_Option::OPTION_WP_CACHE_STATUS);
  }


  /**
   * Activate page caching
   *
   * @return bool
   * @since 1.1.0
   */
  public function activate()
  {
    $this->plugin->option->set(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, 0);

    // Remove old advanced-cache.php.
    if (file_exists(WP_CONTENT_DIR . '/advanced-cache.php'))
      unlink(WP_CONTENT_DIR . '/advanced-cache.php');

    // Copy our own advanced-cache.php.
    $ret = copy(self::LIB_DIR . '/advanced-cache.php', WP_CONTENT_DIR . '/advanced-cache.php');
    if (!$ret)
    {
      $this->log('Failed to copy advanced-cache.php', NULL, Wgpwpp_Log::TYPE_ERROR);
      $this->plugin->option->set(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, 3);
      return false;
    }

    // Create the cache directory
    wp_mkdir_p(CACHE_DIR);

    // Nothing to do if WP_CACHE is already on or forced skip.
    if (defined('WP_CACHE') && WP_CACHE || apply_filters('wgpwpp_cache_skip_config_update', false))
    {
      $this->log('Page caching Enabled');
      $this->plugin->option->set(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, 1);
      return true;
    }

    // Fetch wp-config.php contents.
    $config_path = ABSPATH . 'wp-config.php';
    if (!file_exists(ABSPATH . 'wp-config.php') && @file_exists(dirname(ABSPATH) . '/wp-config.php') && !@file_exists(dirname(ABSPATH) . '/wp-settings.php'))
    {
      $config_path = dirname(ABSPATH) . '/wp-config.php';
    }

    $config = file_get_contents($config_path);

    // Remove existing WP_CACHE definitions.
    // Some regex inherited from https://github.com/wp-cli/wp-config-transformer/
    $pattern = '#(?<=^|;|<\?php\s|<\?\s)(\s*?)(\h*define\s*\(\s*[\'"](WP_CACHE)[\'"]\s*)' . '(,\s*([\'"].*?[\'"]|.*?)\s*)((?:,\s*(?:true|false)\s*)?\)\s*;\s)#ms';

    $config = preg_replace($pattern, '', $config);

    // Add a WP_CACHE to wp-config.php.
    $anchor = "/* That's all, stop editing!";
    if (false !== strpos($config, $anchor))
    {
      $config = str_replace($anchor, "define( 'WP_CACHE', true ); // WEDOS Global WP Cache\n\n" . $anchor, $config);
    }
    elseif (false !== strpos($config, '<?php'))
    {
      $config = preg_replace('#^<\?php\s.*#', "$0\ndefine( 'WP_CACHE', true ); // WEDOS Global WP Cache\n", $config);
    }

    // Write modified wp-config.php.
    $bytes = file_put_contents($config_path, $config);
    $this->plugin->option->set(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, $bytes ? 1 : 2);

    if (!$bytes)
    {
      $this->log('Failed to define WP_CACHE constant.', NULL, Wgpwpp_Log::TYPE_ERROR);
      return false;
    }

    return true;
  }


  /**
   * Deactivate page cachings
   *
   * @return void
   * @since 1.1.0
   */
  public function deactivate()
  {
    $this->plugin->option->set(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, null);

    // Remove advanced-cache.php only if its ours.
    if (file_exists(WP_CONTENT_DIR . '/advanced-cache.php'))
    {
      $contents = file_get_contents(WP_CONTENT_DIR . '/advanced-cache.php');
      if (strpos($contents, 'namespace Wgpwpp_Cache;') !== false)
      {
        unlink(WP_CONTENT_DIR . '/advanced-cache.php');
      }
    }

    $this->log('Page caching Disabled');
  }


  /**
   * Returns flags
   *
   * @param string $set_flag flags to set
   * @return array
   * @since 1.1.0
   */
  private function get_flags(string $set_flag = ''): array
  {
    if (!isset(self::$flags))
    {
      self::$flags = [];
    }

    if ($set_flag)
    {
      self::$flags[] = $set_flag;
    }

    return self::$flags;
  }


  private function execute_event(string $event, $args)
  {
    $events = config('events');

    if (empty($events[$event]))
    {
      return;
    }

    foreach ($events[$event] as $key => $callback)
    {
      $callback($args);
    }
  }


  /**
   * Define required hooks
   *
   * @return void
   * @since 1.1.0
   */
  private function define_hooks()
  {
    if ($this->is_active())
    {
      $this->plugin->get_loader()->add_action('plugin_loaded', $this, 'action_plugin_loaded');
      $this->plugin->get_loader()->add_action('shutdown', $this, 'action_shutdown');
      $this->plugin->get_loader()->add_filter('site_status_tests', $this, 'filter_site_status_tests');
      $this->plugin->get_loader()->add_filter('site_status_page_cache_supported_cache_headers', $this, 'filter_site_status_page_cache_supported_cache_headers');
    }
  }


  public function action_plugin_loaded()
  {
    if (wp_doing_cron())
      $this->plugin->get_loader()->add_action('wgpwpp_wp_cache_delete_expired', $this, 'delete_expired');

    //if ( defined( 'WP_CLI' ) && WP_CLI ) {
    //  include_once( $this->base_dir . '/include/cli.php' );
    //}

    $this->invalidate();
  }


  public function action_shutdown()
  {
    if (!wp_next_scheduled('wgpwpp_cache_delete_expired'))
    {
      wp_schedule_event(time(), 'hourly', 'wgpwpp_cache_delete_expired');
    }
  }


  public function filter_site_status_tests($tests)
  {
    $tests['direct']['wgpwpp_wp_cache'] = ['label' => 'Page Caching Test', 'test' => [$this, 'health_test'],];

    return $tests;
  }


  public function filter_site_status_page_cache_supported_cache_headers($headers)
  {
    $headers['x-cache'] = static function ($value)
    {
      return false !== strpos(strtolower($value), 'hit');
    };
    return $headers;
  }


  public function delete_expired()
  {
    $cache_dir = CACHE_DIR;
    $start = microtime(true);
    $files = [];
    $deleted = 0;
    $time = time();

    $levels = scandir($cache_dir);
    foreach ($levels as $level)
    {
      if ($level == '.' || $level == '..')
      {
        continue;
      }

      if ($level == 'flags.json.php')
      {
        continue;
      }

      if (!is_dir("{$cache_dir}/{$level}"))
      {
        continue;
      }

      $items = scandir("{$cache_dir}/{$level}");
      foreach ($items as $item)
      {
        if ($item == '.' || $item == '..')
        {
          continue;
        }

        if (substr($item, -4) != '.php')
        {
          continue;
        }

        $files[] = "{$cache_dir}/{$level}/{$item}";
      }
    }

    foreach ($files as $filename)
    {
      // Some files after scandir may already be gone/renamed.
      if (!file_exists($filename))
      {
        continue;
      }

      $stat = @stat($filename);
      if (!$stat)
      {
        continue;
      }

      // Skip files modified in the last minute.
      if ($stat['mtime'] + MINUTE_IN_SECONDS > $time)
      {
        continue;
      }

      // Empty file.
      if ($stat['size'] < 1)
      {
        unlink($filename);
        $deleted++;
        continue;
      }

      $f = fopen($filename, 'rb');
      $meta = $this->read_metadata($f);
      fclose($f);

      // This cache entry is still valid.
      if ($meta && !empty($meta['expires']) && $meta['expires'] > $time)
      {
        continue;
      }

      // Delete the cache entry
      unlink($filename);
      $deleted++;
    }

    $end = microtime(true);
    $elapsed = $end - $start;

    if (defined('WP_CLI') && WP_CLI && class_exists('\WP_CLI'))
    {
      \WP_CLI::success(sprintf('Deleted %d/%d files in %.4f seconds', $deleted, count($files), $elapsed));
    }
  }


  /**
   * Read metadata from a file resource.
   *
   * @param resource $f A file resource opened with fopen().
   *
   * @return null|array The decoded cache metadata or null.
   */
  private function read_metadata($f)
  {
    // Skip security header.
    fread($f, strlen('<?php exit; ?>'));

    // Read the metadata length.
    $bytes = fread($f, 4);
    if (!$bytes)
    {
      return;
    }

    $data = unpack('Llength', $bytes);
    if (empty($data['length']))
    {
      return;
    }

    $bytes = fread($f, $data['length']);
    $meta = json_decode($bytes, true);
    return $meta;
  }


  public function filter_woocommerce_product_title($title, $product)
  {
    $this->get_flags(sprintf('post:%d:%d', get_current_blog_id(), $product->get_id()));
    return $title;
  }


  public function expire_flag($flag = null)
  {
    if (!isset(self::$flags_expire))
    {
      self::$flags_expire = [];
    }

    if ($flag)
    {
      self::$flags_expire[] = $flag;
    }

    return self::$flags_expire;
  }


  public function action_transition_post_status($status, $old_status, $post)
  {
    if ($status == $old_status)
    {
      return;
    }

    // Only if the post type is public.
    $obj = get_post_type_object($post->post_type);
    if (!$obj || !$obj->public)
    {
      return;
    }

    $status = get_post_status_object($status);
    $old_status = get_post_status_object($old_status);

    // To or from a public post status.
    if (($status && $status->public) || ($old_status && $old_status->public))
    {
      $this->expire_flag('post_type:' . $post->post_type);
    }
  }


  public function filter_the_posts($posts, $query)
  {
    $post_ids = wp_list_pluck($posts, 'ID');
    $blog_id = get_current_blog_id();

    foreach ($post_ids as $id)
    {
      $this->get_flags(sprintf('post:%d:%d', $blog_id, $id));
    }

    // Nothing else to do if it's a singular query.
    if ($query->is_singular)
    {
      return $posts;
    }

    // If it's a query for multiple posts, then flag it with the post types.
    // TODO: Add proper support for post_type => any
    $post_types = $query->get('post_type');
    if (empty($post_types))
    {
      $post_types = ['post'];
    }
    elseif (is_string($post_types))
    {
      $post_types = [$post_types];
    }

    // Add flags for public post types.
    foreach ($post_types as $post_type)
    {
      $obj = get_post_type_object($post_type);
      if (is_null($obj) || !$obj->public)
      {
        continue;
      }

      $this->get_flags('post_type:' . $post_type);
    }

    return $posts;
  }


  public function action_do_feed_rdf()
  {
    $this->get_flags('feed:' . get_current_blog_id());
  }

  public function action_do_feed_rss()
  {
    $this->get_flags('feed:' . get_current_blog_id());
  }

  public function action_do_feed_rss2()
  {
    $this->get_flags('feed:' . get_current_blog_id());
  }

  public function action_do_feed_atom()
  {
    $this->get_flags('feed:' . get_current_blog_id());
  }

  public function action_clean_post_cache($post_id, $post)
  {
    if (wp_is_post_revision($post))
    {
      return;
    }

    $blog_id = get_current_blog_id();
    $this->expire_flag(sprintf('post:%d:%d', $blog_id, $post_id));
  }


  public function action_init()
  {
    if ($this->plugin::is_multisite())
    {
      $this->get_flags(sprintf('network:%d:%d', get_current_network_id(), get_current_blog_id()));
    }
  }


  public function action_shutdown_invalidate()
  {
    $flush_actions = ['activate_plugin', 'deactivate_plugin', 'switch_theme', 'customize_save', 'update_option_permalink_structure', 'update_option_tag_base', 'update_option_category_base', 'update_option_WPLANG', 'update_option_blogname', 'update_option_blogdescription', 'update_option_blog_public', 'update_option_show_on_front', 'update_option_page_on_front', 'update_option_page_for_posts', 'update_option_posts_per_page', 'update_option_woocommerce_permalinks',];

    $flush_actions = apply_filters('wgpwpp_cache_flush_actions', $flush_actions);

    $ms_flush_actions = ['_core_updated_successfully', 'automatic_updates_complete',];

    $expire_flag = is_multisite() ? sprintf('network:%d:%d', get_current_network_id(), get_current_blog_id()) : '/';

    foreach ($flush_actions as $action)
    {
      if (did_action($action))
      {
        $this->expire_flag($expire_flag);
        break;
      }
    }

    // Multisite flush actions expire the entire network.
    foreach ($ms_flush_actions as $action)
    {
      if (did_action($action))
      {
        $this->expire_flag('/');
        break;
      }
    }

    $expire = $this->expire_flag();
    if (empty($expire))
    {
      return;
    }

    $flags = null;
    $path = CACHE_DIR . '/flags.json.php';
    $exists = file_exists($path);
    $mode = $exists ? 'r+' : 'w+';

    // Make sure cache dir exists.
    if (!$exists && !wp_mkdir_p(CACHE_DIR))
    {
      return;
    }

    $f = fopen($path, $mode);
    $length = filesize($path);

    flock($f, LOCK_EX);

    if ($length)
    {
      $flags = fread($f, $length);
      $flags = substr($flags, strlen('<?php exit; ?>'));
      $flags = json_decode($flags, true);
    }

    if (!$flags)
    {
      $flags = [];
    }

    foreach ($expire as $flag)
    {
      $flags[$flag] = time();
    }

    if (!wp_mkdir_p(CACHE_DIR))
    {
      return;
    }

    if ($length)
    {
      ftruncate($f, 0);
      rewind($f);
    }

    fwrite($f, '<?php exit; ?>' . json_encode($flags));
    fclose($f);

    $this->execute_event('expire', ['flags' => $expire]);
  }


  public function action_update_option_rss_use_excerpt()
  {
    $this->expire_flag('feed:' . get_current_blog_id());
  }

  public function action_update_option_posts_per_rss()
  {
    $this->expire_flag('feed:' . get_current_blog_id());
  }


  private function invalidate()
  {

    // WooCommerce has some internal WP_Query extensions with transient caching,
    // so the_posts and other core filters will often not run. Getting the product
    // title however is a good indication that a product appears on some page.
    $this->plugin->get_loader()->add_filter('woocommerce_product_title', $this, 'filter_woocommerce_product_title', 10, 2);

    // When a post is published, or unpublished, we need to invalidate various
    // different pages featuring that specific post type.
    $this->plugin->get_loader()->add_action('transition_post_status', $this, 'action_transition_post_status', 10, 3);

    // Filter WP_Query at the stage where the query was completed, the results have
    // been fetched and sorted, as well as accounted and offset for sticky posts.
    // Here we attempt to guess which posts appear on this requests and set flags
    // accordingly. We also attempt to set more generic flags based on the query.
    $this->plugin->get_loader()->add_filter('the_posts', $this, 'filter_the_posts', 10, 2);

    // Flag feeds
    $this->plugin->get_loader()->add_action('do_feed_rdf', $this, 'action_do_feed_rdf');
    $this->plugin->get_loader()->add_action('do_feed_rss', $this, 'action_do_feed_rss');
    $this->plugin->get_loader()->add_action('do_feed_rss2', $this, 'action_do_feed_rss2');
    $this->plugin->get_loader()->add_action('do_feed_atom', $this, 'action_do_feed_atom');

    // Expire flags when post cache is cleaned.
    $this->plugin->get_loader()->add_action('clean_post_cache', $this, 'filter_clean_post_cache', 10, 2);

    // Multisite network/blog flags.
    $this->plugin->get_loader()->add_action('init', $this, 'action_init');

    // Last-minute expirations, save flags.
    $this->plugin->get_loader()->add_action('shutdown', $this, 'action_shutdown_invalidate');

    $this->plugin->get_loader()->add_action('update_option_rss_use_excerpt', $this, 'action_update_option_rss_use_excerpt');
    $this->plugin->get_loader()->add_action('update_option_posts_per_rss', $this, 'action_update_option_posts_per_rss');
  }


  public function health_test(): array
  {
    $result = array('label' => __('WEDOS Global page caching is enabled', 'wgpwpp'), 'status' => 'good', 'badge' => ['label' => __('Performance'), 'color' => 'blue',], 'description' => '<p>' . __('Page caching loads your site faster for visitors, and allows your site to handle more traffic without overloading.', 'wgpwpp') . '</p>', 'actions' => '', 'test' => 'wgpwpp_wp_cache',);

    $installed = $this->plugin->option->get(Wgpwpp_Option::OPTION_WP_CACHE_STATUS, false);

    $actions = sprintf('<p><a href="%s">%s</a></p>', esc_url(admin_url('admin.php?page=wgpwpp_cache')), __('Manage your plugins', 'wgpwpp'));

    if ($installed === false || $installed > 1)
    {
      $result['status'] = 'critical';
      $result['label'] = __('WEDOS Global page caching is not installed correctly', 'wgpwpp');
      $result['description'] = '<p>' . __('Looks like the WEDOS Global cache is not installed correctly. Please try to deactivate and activate it again on the plugin cache setting page.', 'wgpwpp') . '</p>';
      $result['actions'] = $actions;
      $result['badge']['color'] = 'red';
      return $result;
    }

    if ($installed === 0)
    {
      $result['status'] = 'critical';
      $result['label'] = __('WEDOS Global page caching is being installed', 'wgpwpp');
      $result['description'] = '<p>' . __('WEDOS Global cache is being installed. This should only take a few seconds. If this message does not disappear, please try to deactivate and activate the the cache on the plugin cache setting page.', 'wgpwpp') . '</p>';
      $result['actions'] = $actions;
      $result['badge']['color'] = 'orange';
      return $result;
    }

    if (!defined('WP_CACHE') || !WP_CACHE)
    {
      $result['status'] = 'critical';
      $result['label'] = __('Page caching is disabled in wp-config.php', 'wgpwpp');
      $result['description'] = '<p>' . __('WEDOS Global cache is installed, but caching is disabled because the WP_CACHE directive is not defined in wp-config.php. Please try to deactivate and activate the cache on plugin cache setting page, or define WP_CACHE manually in wp-config.php', 'wgpwpp') . '</p>';
      $result['actions'] = $actions;
      $result['badge']['color'] = 'red';
      return $result;
    }

    if (!file_exists(WP_CONTENT_DIR . '/advanced-cache.php'))
    {
      $result['status'] = 'critical';
      $result['label'] = __('WEDOS Global page caching is not installed correctly', 'wgpwpp');
      $result['description'] = '<p>' . __('Looks like the WEDOS Global cache is not installed correctly, advanced-cache.php is missing. Please try to deactivate and activate the cache on the WEDOS Global plugin cache setting page.', 'wgpwpp') . '</p>';
      $result['actions'] = $actions;
      $result['badge']['color'] = 'red';
      return $result;
    }

    $contents = file_get_contents(WP_CONTENT_DIR . '/advanced-cache.php');
    if (strpos($contents, 'namespace Wgpwpp_Cache;') === false)
    {
      $result['status'] = 'critical';
      $result['label'] = __('WEDOS Global page caching is not installed correctly', 'wgpwpp');
      $result['description'] = '<p>' . __('Looks like the WEDOS Global cache is not installed correctly, invalid advanced-cache.php contents. Please try to deactivate and activate the cache on the WEDOS Global plugin cache setting page.', 'wgpwpp') . '</p>';
      $result['actions'] = $actions;
      $result['badge']['color'] = 'red';
      return $result;
    }

    if (!is_writable(CACHE_DIR))
    {
      $result['status'] = 'critical';
      $result['label'] = __('Page caching directory is missing or not writable', 'wgpwpp');
      $result['description'] = '<p>' . __('WEDOS Global cache is installed, but the cache directory is missing or not writable. Please check the wp-content/cache directory permissions in your hosting environment, then toggle the cache status on WEDOS Global plugin cache setting page.', 'wgpwpp') . '</p>';
      $result['actions'] = $actions;
      $result['badge']['color'] = 'red';
      return $result;
    }

    return $result;
  }


  /**
   * Try to detect active cache plugins
   *
   * @return array
   * @since 1.1.2
   */
  public function detect_cache_plugins(): array
  {
    $advanced_caching = false;
    if (file_exists(WP_CONTENT_DIR . '/advanced-cache.php'))
    {
      $contents = file_get_contents(WP_CONTENT_DIR . '/advanced-cache.php');
      if (strstr($contents, 'namespace Wgpwpp_Cache;') === false)
        $advanced_caching = true;
    }

    $wp_cache_constant = defined('WP_CACHE') && WP_CACHE;

    $known_cache_plugins = [];
    $plugins = get_plugins();
    foreach ($plugins as $slug => $data)
    {
      if (!in_array($slug, self::$known_cache_plugins_slugs))
        continue;

      if (!is_plugin_active($slug))
        continue;

      $known_cache_plugins[$slug] = $data['Name'] ?? $slug;
    }

    return [
      'advanced-caching' => $advanced_caching,
      'wp_cache_constant' => $wp_cache_constant,
      'known-cache-plugins' => $known_cache_plugins,
    ];
  }


  /**
   * Flushes cache
   *
   * @since 1.1.2
   * @return void
   */
  public function flush_cache()
  {
    $this->delete_cache_files(CACHE_DIR);
    $this->log('FLUSH CACHE');
  }


  /**
   * Deletes cache files
   *
   * @since 1.1.2
   * @param string $path path
   * @return void
   */
  private function delete_cache_files(string $path)
  {
    if (is_file( $path ))
    {
      unlink( $path );
      return;
    }

    $entries = scandir( $path );
    foreach ( $entries as $entry ) {
      if ( $entry == '.' || $entry == '..' )
        continue;

      $this->delete_cache_files( $path . '/' . $entry );
    }

    if ($path !== CACHE_DIR)
      rmdir( $path );
  }


  /**
   * Log for WP cache
   *
   * @param string $msg
   * @param WP_Error|mixed $data
   * @param string $type
   * @return void
   * @since 1.1.0
   */
  private function log(string $msg, $data = NULL, string $type = Wgpwpp_Log::TYPE_INFO)
  {
    $msg = "\tWPCACHE :: " . $msg;
    $this->plugin->log->write($msg, $data, $type);
  }
}