<?php
if (!defined('ABSPATH'))
  exit;

if (!function_exists('get_plugins'))
  require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Class - class responsible for logging
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Log
{
  /**
   * Log type - INFO
   *
   * @since 1.0.0
   */
  public const TYPE_INFO = 'info';

  /**
   * Log type - ERROR
   *
   * @since 1.0.0
   */
  public const TYPE_ERROR = 'error';

  /**
   * WEDOS support email address for sending debug logs
   *
   * @since 1.0.0
   */
  private const SUPPORT_EMAIL = 'hosting@wedos.com';

	/**
	 * Plugin instance
	 *
	 * @since 1.0.0
	 * @var Wgpwpp
	 */
	private Wgpwpp $plugin;


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
	}


  /**
   * Returns log header
   *
   * @since 1.0.0
   * @return string
   */
  private function get_log_header(): string
  {
    $header = "Server info:".PHP_EOL;
    $header .= "============".PHP_EOL;
    $header .= print_r($_SERVER, true).PHP_EOL.PHP_EOL;

    $header .= "WordPress info:".PHP_EOL;
    $header .= "===============".PHP_EOL;
    $header .= 'PHP Version: '.$this->plugin->get_php_version().PHP_EOL;
    $header .= 'WordPress Version: '.$this->plugin->get_wp_version().PHP_EOL;
    $header .= 'WordPress Admin E-mail: '.$this->plugin->get_admin_email().PHP_EOL;
    $header .= 'WordPress URL: '.get_bloginfo('wpurl').PHP_EOL;
    $header .= 'WordPress Domain: '.$this->plugin->get_host().PHP_EOL;
    $header .= 'WGPWPP Version: '.$this->plugin->get_version().PHP_EOL;
    $header .= 'WGPWPP URL: '.$this->plugin->get_admin_page_url().PHP_EOL;

    $plugins = get_plugins();
    if (is_array($plugins))
      $header .= 'Installed plugins: '.PHP_EOL.print_r($plugins, true).PHP_EOL.PHP_EOL;
    else
      $header .= PHP_EOL;

    $service = $this->plugin->get_service();
    $header .= "Service info:".PHP_EOL;
    $header .= "=============".PHP_EOL;
    $header .= "Service name: ".$service->get_service_name().PHP_EOL;
    $header .= "Service TLD: ".$service->get_service_tld().PHP_EOL;
    $header .= "Service state: ".$service->get_service_state().PHP_EOL;
    $header .= "Service state data: ".print_r($service->get_service_state_data(), true).PHP_EOL;

    return $header;
  }


  /**
   * Log write
   *
   * @param string $msg log message
   * @param WP_Error|mixed $data additional data
   * @param string $type log type (constants self::TYPE_*]
   * @return void
   * @since 1.0.0
   */
  public function write(string $msg, $data = NULL, string $type = self::TYPE_INFO)
  {
    $log = $this->plugin->option->get(Wgpwpp_Option::OPTION_LOG);
    if (!is_array($log))
      $log = [];

    $msg_orig = $msg;

    try
    {
      $time = new DateTime('now', new DateTimeZone('UTC'));
      $time = $time->format('Y-m-d H:i:s.u');
    }
    catch (Exception $e)
    {
      return;
    }

    $msg = $time."\t";

    switch ($type)
    {
      case self::TYPE_INFO: $msg .= "[INFO]\t"; break;
      case self::TYPE_ERROR: $msg .= "[ERROR]\t"; break;
      default: $msg .= "[INFO]\t";
    }

    $msg .= $msg_orig;
    $msg .= PHP_EOL;

    if (!is_null($data))
    {
      if ($data instanceof WP_Error)
      {
        $codes = $data->get_error_codes();
        $errors = [];

        foreach ($codes as $code)
        {
          $errors[$code] = [
            'message' => $data->get_error_message($code),
            'data'    => $data->get_error_data($code),
          ];
        }

        $data = $errors;
      }

      if ($data)
      {
        $msg .= "==========================================" . PHP_EOL;
        $msg .= print_r($data, true).PHP_EOL;
        $msg .= PHP_EOL."==========================================" . PHP_EOL;
        $msg .= PHP_EOL;
      }
    }

    $log[] = $msg;
    $this->plugin->option->set(Wgpwpp_Option::OPTION_LOG, $log);
  }


  /**
   * Delete log file
   *
   * @since 1.0.0
   * @return void
   */
  public function delete()
  {
    $this->plugin->option->set(Wgpwpp_Option::OPTION_LOG, null);
  }


  /**
   * Download log file
   *
   * @since 1.0.0
   * @return bool
   */
  public function download(): bool
  {
    $header = $this->get_log_header().PHP_EOL;
    $header .= "Log:".PHP_EOL;
    $header .= "====".PHP_EOL.PHP_EOL;

    $log = $this->plugin->option->get(Wgpwpp_Option::OPTION_LOG);
    if (!is_array($log))
      $log = '';
    else
      $log = implode("\n", $log);

    $hostname = $this->plugin->get_host();
    if (is_wp_error($hostname))
      return false;

    $log = $header.$log;

    $filename = 'wgpwpp-'.str_replace('.', '-', $hostname).'.log';

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=".$filename);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".strlen($log));

    $output = fopen('php://output', 'w');
    fwrite($output, $log);
    fclose($output);
    exit;
  }


  /**
   * Sends log file to WEDOS support
   *
   * @since 1.0.0
   * @return bool|mixed
   */
  public function send_log()
  {
    $header = $this->get_log_header().PHP_EOL;
    $header .= "Log:".PHP_EOL;
    $header .= "====".PHP_EOL.PHP_EOL;

    $log = $this->plugin->option->get(Wgpwpp_Option::OPTION_LOG);
    if (!is_array($log))
      $log = '';
    else
      $log = implode(PHP_EOL, $log);

    $hostname = $this->plugin->get_host();
    if (is_wp_error($hostname))
      return false;

    $subject = sprintf("WGP WordPress Plugin - %s - DEBUG report", $hostname);

    $msg = "WGP WordPress Plugin - DEBUG report".PHP_EOL.PHP_EOL;
    $msg .= $header.$log;

    $headers = [
      'Content-Type: text/plain; charset=UTF-8',
      sprintf('From: WGPWPP %s <wordpress@%s>', $hostname, $hostname),
    ];

    return wp_mail(self::SUPPORT_EMAIL, $subject, $msg, $headers);
  }
}