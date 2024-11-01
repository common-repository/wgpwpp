<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for admin layout
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Service_Layout extends Wgpwpp_Layout
{
  /**
   * OAuth2 client instance
   *
   * @since 1.0.0
   * @var Wgpwpp_Client
   */
	private Wgpwpp_Client $client;

  /**
   * WGP service instance
   *
   * @since 1.0.0
   * @var Wgpwpp_Service
   */
	private Wgpwpp_Service $service;


  /**
   * Constructor
   *
   * @since 1.0.0
   * @param Wgpwpp $plugin plugin instance
   */
  public function __construct(Wgpwpp $plugin)
  {
    parent::__construct($plugin);
    $this->client = $plugin->get_client();
    $this->service = $plugin->get_service();
  }


  /**
   * Register all of the hooks related to the render layout.
   *
   * @since    1.0.0
   * @access   private
   */
	protected function define_hooks()
	{
		$this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_layout_step', $this, 'action_step_process');
		$this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_layout_verification_code', $this, 'action_verification_code');
		$this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_layout_service_info', $this, 'action_service_info');
		$this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_layout_service_create', $this, 'action_service_create');
		$this->plugin->get_loader()->add_action('wp_ajax_wgpwpp_layout_service_retry_state', $this, 'action_service_retry_state');
	}


  /**
   * Sets required step into URL
   *
   * @since 1.0.0
   * @return void
   */
	public function action_step_process()
	{
		// check nonce
		if (!check_ajax_referer( $this->plugin->get_nonce_name( 'wgpwpp_layout_step'), false, false))
			wp_die(json_encode(['result' => 'error', 'msg' => __('Invalid request!', 'wgpwpp')]));

    $step = sanitize_text_field($_POST['wgpwpp_step']);

		wp_die(json_encode([
			'result'    => 'success',
			'data'      => [
				'redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$step,
			],
    ]));
	}


  /**
   * Loads service info from API
   *
   * @since 1.0.0
   * @return void
   */
	public function action_service_info()
	{
		$step = isset($_POST['wgpwpp_step']) ? sanitize_text_field($_POST['wgpwpp_step']) : $this->get_step();

		// check nonce
		if (!check_ajax_referer( $this->plugin->get_nonce_name( 'wgpwpp_layout_service_info'), false, false))
		{
			$this->plugin->admin_section->notices->error(__('Invalid request. Please refresh current page and try it again.', 'wgpwpp'), true);
			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$step.'&service_error=1']));
		}

		$state_current = $this->service->get_service_state();

		// load service info
		$service_info = $this->plugin->get_service()->info();
		if ($service_info === true)
		{
			if ($state_current === $this->service->get_service_state())
			{
        $type = 'warning';

				switch ($state_current)
				{
					case $this->service::STATE_PENDING_NS:
						$msg = __("We are still unable to resolve required DNS servers by the domain. This may take up few hours. Be patient please.", 'wgpwpp');
						break;

					case $this->service::STATE_PENDING_TXT:
						$msg = __("We are still unable to resolve required TXT record. This may take up one hour based on TTL. Be patient please.", 'wgpwpp');
						break;

					case $this->service::STATE_PENDING_CRT:
						$msg = __("TLS certificate for encrypted data transfer is still generating. This may take few minutes. Please be patient.", 'wgpwpp');
						break;

          case $this->service::STATE_ACTIVE:
            if (!$this->service->is_pointing_to_proxy())
            {
              $msg = sprintf(__('Your domain %s is still not directed to our proxy servers by setting the DNS records in the table below.', 'wgpwpp'), $this->service->get_service_name());
            }
            else
            {
              $service_data = $this->service->get_service_data();
              if ($service_data)
                $status = $service_data['status'];
              else
                $status = 'active';

              switch ($status)
              {
                case 'disabled':
                  $msg = __('The service is stopped!', 'wgpwpp');
                  $type = 'error';
                  break;

                case 'expired':
                  $msg = __('The service is expired!', 'wgpwpp');
                  $type = 'warning';
                  break;

                default:
                  $type = 'success';
                  $msg = __('WEDOS Global service is properly configured to protect you website.', 'wgpwpp');
              }
            }
            break;

					default: $msg = '';
				}

				if (!empty($msg))
					$this->plugin->admin_section->notices->$type($msg, true);
			}

			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$this->get_next_step().'&info=0']));
		}
		elseif (is_wp_error($service_info))
		{
      if ($service_info->get_error_code() === 4)
        wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$this->get_next_step()]));

			switch ($service_info->get_error_code())
			{
				case 1:
					$this->plugin->admin_section->notices->critical(__("Failed to setup service. The plugin is unable to parse your WordPress installation domain name!", 'wgpwpp'), true);
					break;

				case 3:
					$this->plugin->admin_section->notices->error(__("Your domain name %s is not valid for WEDOS Global service.", 'wgpwpp'), true);
					break;

				default:
					$this->plugin->admin_section->notices->error(__("The plugin is unable to connect with WEDOS Global API. Please try again later", 'wgpwpp').' '.$service_info->get_error_message(), true);
			}
			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$step.'&service_error=1&info=0']));
		}
		else
		{
			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$step.'&service_error=1&info=0']));
		}
	}


  /**
   * Creates WGP service
   *
   * @since 1.0.0
   * @return void
   */
	public function action_service_create()
	{
		// check nonce
		if (!check_ajax_referer( $this->plugin->get_nonce_name( 'wgpwpp_layout_service_create'), false, false))
		{
			$this->plugin->admin_section->notices->error(__('Invalid request. Please refresh current page and try it again.', 'wgpwpp'), true);
			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step=2&service_error=1']));
		}

		// process error
		$process_error = function (WP_Error $error)
		{
			switch ($error->get_error_code())
			{
				case 1:
					$this->plugin->admin_section->notices->critical(__("Failed to setup service. The plugin is unable to parse your WordPress installation domain name!", 'wgpwpp'), true);
					break;

				case 3:
					$this->plugin->admin_section->notices->error(sprintf(__("Your domain name %s is not valid for WEDOS Global service.", 'wgpwpp'), $this->plugin->get_host()), true);
					break;

				case 5:
					$this->plugin->admin_section->notices->error(sprintf(__("Unfortunately, it is not possible to add the subdomain %s on which your WordPress website is hosted.", 'wgpwpp'), $this->plugin->get_host()), true);
					$this->plugin->admin_section->notices->error(__("The WEDOS Global service is intended only for second level domains.", 'wgpwpp'), true);
          $subject = urlencode(sprintf(__("WGPWPP - How to secure my WordPress subdomain %s", 'wgpwpp'), $this->plugin->get_host()));
          $href = "https://client.wedos.com/contact/cform.html?nologin=1&subject=".$subject;
					$this->plugin->admin_section->notices->info(sprintf(__("Please <a href='%s' target='_blank'>contact</a> our support for information on how to protect your website.", 'wgpwpp'), $href), true);
					break;

        case 6:
          $this->plugin->admin_section->notices->error(sprintf(__("Unfortunately, it is not possible to add the subdomain %s on which your WordPress website is hosted.", 'wgpwpp'), $this->plugin->get_host()), true);
          $this->plugin->admin_section->notices->error(sprintf(__("Unsupported TLD.", 'wgpwpp'), $this->plugin->get_host()), true);
          break;

				default:
					$this->plugin->admin_section->notices->error(__("The plugin is unable to connect with WEDOS Global API. Please try again later.", 'wgpwpp').' '.$error->get_error_message(), true);
			}
			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step=2&service_error=1']));
		};

		// load service info
		$service_info = $this->plugin->get_service()->info();
		if ($service_info === true && !$this->plugin->get_service()->is_disabled())
			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$this->get_next_step()]));
		elseif (is_wp_error($service_info) && $service_info->get_error_code() !== 4)
			$process_error($service_info);

		// service create
		$service_create = $this->plugin->get_service()->create();
		if ($service_create === true)
			wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$this->get_next_step()]));
		elseif (is_wp_error($service_create))
			$process_error($service_create);
	}


  /**
   * Retry TLS certificate generation
   *
   * @return void
   * @since 1.2.2
   */
  public function action_service_retry_state()
  {
    // check nonce
    if (!check_ajax_referer( $this->plugin->get_nonce_name( 'wgpwpp_layout_service_retry_state'), false, false))
    {
      $this->plugin->admin_section->notices->error(__('Invalid request. Please refresh current page and try it again.', 'wgpwpp'), true);
      wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step=4&service_error=1']));
    }

    $retry = $this->service->retry_state();
    if ($retry === true)
    {
      wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step='.$this->get_next_step()]));
    }
    else
    {
      $this->plugin->admin_section->notices->error($retry->get_error_message(), true);
      wp_die(json_encode(['redirect_uri' => $this->plugin->get_admin_page_url().'&step=4&service_error=1']));
    }
  }


  /**
   * Shows page with verification code
   *
   * @since 1.0.0
   * @return void
   */
	public function action_verification_code()
	{

		// check nonce
		if (!check_ajax_referer( $this->plugin->get_nonce_name( 'wgpwpp_layout_verification_code'), false, false))
		{
			$this->plugin->admin_section->notices->error(__('Failed to show verification code. Please start the plugin registration process again.', 'wgpwpp'), true);
			wp_die(json_encode(['error' => true]));
		}

		$data = $this->plugin->admin_section->authorization->get_current_code();
		if (!$data)
		{
			$this->plugin->admin_section->notices->error(__('Failed to show verification code. Please start the plugin registration process again.', 'wgpwpp'), true);
			wp_die(json_encode(['error' => true]));
		}

		wp_die(json_encode(['code' => $data['verifier']]));
	}


	/**
	 * Returns required step
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_step(): int
	{
		$step_next = $this->get_next_step();

		$required_step = isset($_GET['step']) ? sanitize_text_field($_GET['step']) : null;
		if (!is_null($required_step) && $step_next >= $required_step)
			return $required_step;

		return $step_next;
	}


	/**
	 * Returns number of next step based on service state
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_next_step()
	{
		if (!$this->client->is_registered())
			return 1;

		if (!$this->service->is_created())
			return 2;

		if ($this->service->is_stucked() || $this->service->is_pending_ns() || !$this->service->is_verified())
			return 3;

		if (!$this->service->is_pointing_to_proxy() || $this->service->is_pending_crt() || $this->service->is_error_crt())
			return 4;

		return 5;
	}


	/**
	 * Renders layout
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render()
	{
    $reload_info = !isset($_GET['info']) || sanitize_text_field($_GET['info']);

    // page refresh detection
    $refresh = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
    if ($refresh)
      $reload_info = true;

    // reloading service info
		if ($this->client->is_registered() && $this->service->is_created() && $reload_info)
			$this->service->info();

		$required_step = $this->get_step();

		try
		{
			$render_step = 'render_step_'.$required_step;
			$this->$render_step();
		}
		catch (\Throwable $e) {
			// TODO
			echo '<pre>';
			print_r($e);
			print_r($e->getMessage());
			print_r($e->getTrace());
			echo '</pre>';
		}
	}


  /**
   * Renders step - #1
   *
   * @since 1.0.0
   * @return void
   */
	private function render_step_1()
	{
		$this->latte->render('1.latte', new Wgpwpp_Service_Layout_Parameters_Step1($this->plugin));
	}


  /**
   * Renders step - #2
   *
   * @since 1.0.0
   * @return void
   */
	private function render_step_2()
	{
		$this->latte->render('2.latte', new Wgpwpp_Service_Layout_Parameters_Step2($this->plugin));
	}


  /**
   * Renders step - #3
   *
   * @since 1.0.0
   * @return void
   */
	private function render_step_3()
	{
		$this->latte->render('3.latte', new Wgpwpp_Service_Layout_Parameters_Step3($this->plugin));
	}


  /**
   * Renders step - #4
   *
   * @since 1.0.0
   * @return void
   */
	private function render_step_4()
	{
		$this->latte->render('4.latte', new Wgpwpp_Service_Layout_Parameters_Step4($this->plugin));
	}


  /**
   * Renders step - #5
   *
   * @since 1.0.0
   * @return void
   */
	private function render_step_5()
	{
		$this->latte->render('5.latte', new Wgpwpp_Service_Layout_Parameters_Step5($this->plugin));
	}
}


abstract class Wgpwpp_Service_Layout_Parameters_Global extends Wgpwpp_Layout_Parameters
{
	public bool $error;

	public int $step;

	public int $next_step;

	public string $nonce_step;

	public string $nonce_service_info;


	public function __construct(Wgpwpp $plugin)
	{
    parent::__construct($plugin);

    $error = isset($_GET['service_error']) ? (int)sanitize_text_field($_GET['service_error']) : false;
		$this->error = $error === 1;
		$this->step = $this->get_step();
		$this->next_step = $this->plugin->admin_section->layout->get_next_step();
		$this->nonce_step = wp_create_nonce($plugin->get_nonce_name( 'wgpwpp_layout_step'));
		$this->nonce_service_info = wp_create_nonce($plugin->get_nonce_name( 'wgpwpp_layout_service_info'));
	}


	abstract protected function get_step(): int;
}


final class Wgpwpp_Service_Layout_Parameters_Step1 extends Wgpwpp_Service_Layout_Parameters_Global
{
	public string $user_email;

	public string $nonce_auth;

	public string $nonce_code;

	public bool $undelivered_code;

	public function __construct( Wgpwpp $plugin )
	{
		parent::__construct( $plugin );
		$this->user_email = $plugin->get_admin_email();
		$this->nonce_auth = wp_create_nonce($plugin->get_nonce_name( 'wgpwpp_auth_redirect'));
		$this->nonce_code = wp_create_nonce($plugin->get_nonce_name( 'wgpwpp_layout_verification_code'));
		$this->undelivered_code = isset($_GET['undelivered_code']) && sanitize_text_field($_GET['undelivered_code']);
	}


	protected function get_step(): int {
		return 1;
	}
}


final class Wgpwpp_Service_Layout_Parameters_Step2 extends Wgpwpp_Service_Layout_Parameters_Global
{
	public Wgpwpp_Service $service;

	public string $nonce_service;

	public function __construct( Wgpwpp $plugin )
	{
		parent::__construct( $plugin );
		$this->service = $plugin->get_service();
		$this->nonce_service = wp_create_nonce($plugin->get_nonce_name( 'wgpwpp_layout_service_create'));
	}

	protected function get_step(): int {
		return 2;
	}
}


final class Wgpwpp_Service_Layout_Parameters_Step3 extends Wgpwpp_Service_Layout_Parameters_Global
{
	public Wgpwpp_Service $service;

	public array $txt_data = [];

	public array $ns_data = [];


	public function __construct( Wgpwpp $plugin )
	{
		parent::__construct( $plugin );
		$this->service = $plugin->get_service();

		if (!$this->service->is_verified())
		{
			$txt_data = $plugin->get_service()->get_service_state_data();
			if (is_array($txt_data))
				$this->txt_data = $txt_data;
		}
		elseif ($this->service->is_pending_ns())
		{
			$ns_data = $this->service->get_service_state_data();
			if ($ns_data && isset($ns_data['assigned']) && is_array($ns_data['assigned']))
				$this->ns_data = $ns_data['assigned'];
		}
	}

	protected function get_step(): int {
		return 3;
	}
}


final class Wgpwpp_Service_Layout_Parameters_Step4 extends Wgpwpp_Service_Layout_Parameters_Global
{
	public Wgpwpp_Service $service;

	public string $dns_approval_url;

	public string $dns_admin_link;

	public array $proxy_ips = [];

	public array $dns_records = [];

	public array $dns_records_new = [];

	public Wgpwpp_Notice $dns_warning;

  public string $nonce_service_retry_state;

  public ?string $wgp_url;

	public function __construct( Wgpwpp $plugin )
	{
		parent::__construct( $plugin );
		$this->service = $plugin->get_service();
    $this->nonce_service_retry_state = wp_create_nonce($plugin->get_nonce_name( 'wgpwpp_layout_service_retry_state'));
    $this->wgp_url = $this->service->get_service_url();

		if (!$this->service->is_dns_approved() || !$this->service->is_pointing_to_proxy())
		{
			$data  = $this->service->get_service_state_data();
      $this->proxy_ips = isset($data['dns_ip_addresses']) && is_array($data['dns_ip_addresses']) ? $data['dns_ip_addresses'] : [];
      $this->dns_records = isset($data['dns_records']) && is_array($data['dns_records']) ? $data['dns_records'] : [];
      $this->dns_records_new = isset($data['dns_records_new']) && is_array($data['dns_records_new']) ? $data['dns_records_new'] : [];

      $warning_msg = sprintf(__('In the table below you can see all DNS records we have detected for your domain %s.', 'wgpwpp'), $this->service->get_service_name());
      $warning_msg .= '<br><strong>'.__('We strongly recommend to check the DNS records if there is no one missing.', 'wgpwpp').'</strong>';
      $this->dns_warning = $this->plugin->admin_section->notices->warning($warning_msg);

      $this->dns_approval_url = $data['dns_approval_url'] ?? '';
      $this->dns_admin_link = $this->service::DNS_ADMIN_LINK;
    }
	}


	protected function get_step(): int {
		return 4;
	}
}


final class Wgpwpp_Service_Layout_Parameters_Step5 extends Wgpwpp_Service_Layout_Parameters_Global
{
	const WGP_URL = 'https://client.wedos.global/';

	public Wgpwpp_Service $service;

  public array $proxy_ips = [];

  public ?array $service_data = [];

  public string $service_state;

  public string $service_expiration;

  public Wgpwpp_Notice $trial_notice;

  public Wgpwpp_Notice $status_notice;

  public Wgpwpp_Notice $order_notice;

	public ?string $wgp_url;

	public function __construct( Wgpwpp $plugin )
	{
		parent::__construct( $plugin );
		$this->service = $plugin->get_service();
    $this->wgp_url = $this->service->get_service_url();
    $data = $this->service->get_service_state_data();
    $this->proxy_ips = isset($data['dns_ip_addresses']) && is_array($data['dns_ip_addresses']) ? $data['dns_ip_addresses'] : [];
    $this->service_data = $this->service->get_service_data();

    if ($this->service_data)
    {
      $this->service_expiration = date_i18n(get_option('date_format'), strtotime($this->service_data['expiration']));

      if ($this->service_data['is_trial'])
      {
        $expiration = new DateTime($this->service_data['expiration'], new DateTimeZone('Europe/Prague'));
        $diff = $expiration->diff(new DateTime(date('Y-m-d'), new DateTimeZone(('Europe/Prague'))));

        $message = __('The service is in TRIAL mode', 'wgpwpp');
        if ($diff->invert)
          $message .= ' ' . sprintf(_n('(%s day left).', '(%s days left).', $diff->days, 'wgpwpp'), number_format_i18n($diff->days));
        $message = '<strong>'.$message.'</strong>';

        $message .= ' <a href="' . $this->service_data['upgrade_link'] . '" target="_blank">' . __('Upgrade the service to the full version', 'wgpwpp') . '</a>';
        $this->trial_notice = $this->plugin->admin_section->notices->warning($message);
      }

      switch ($this->service_data['status'])
      {
        case 'disabled':
          $message = '<strong>' . __('The service is stopped.', 'wgpwpp') . '</strong>';
          $message .= ' <a href="' . $this->wgp_url . '" target="_blank">' . __('Go to the Dashboard', 'wgpwpp') . '</a>';
          $message .= ' ' . __('or', 'wgpwpp') . ' <a href="https://client.wedos.com/contact/cform.html?nologin=1" target="_blank">' . __('Contact support', 'wgpwpp') . '</a>';
          $message .= ' ' . __('for more information', 'wgpwpp');
          $this->status_notice = $this->plugin->admin_section->notices->error($message);
          $this->service_state = '<span style="color: red;">'.$this->service_data['status_desc'].'</span>';
          break;

        case 'expired':
          $message = '<strong>' . __('The service is expired.', 'wgpwpp') . '</strong>';
          if (!isset($this->service_data['pending_order'])) {
            $message .= ' <a href="' . $this->service_data['renewal_link'] . '" target="_blank">' . __('Order service renewal', 'wgpwpp') . '</a>';
          } else {
            $message .= ' <strong>' . __("There is pending renewal order waiting for", 'wgpwpp').'</strong>';
            $message .= ' <a href="' . $this->service_data['pending_order']['orderpay_url'] . '" target="_blank">' . __("payment", 'wgpwpp') . '</a>';
          }
          $this->status_notice = $this->plugin->admin_section->notices->warning($message);
          $this->service_state = '<span style="color: red;">'.$this->service_data['status_desc'].'</span>';
          break;

        default:
          if ($this->service_data['status'] === 'active')
            $this->service_state = '<span style="color: green;">'.$this->service_data['status_desc'].'</span>';
          else
            $this->service_state = '<span>'.$this->service_data['status_desc'].'</span>';
      }

      if (isset($this->service_data['pending_order']) && $this->service_data['status'] !== 'expired')
      {
        $message = ' <strong>' . __("There is pending renewal order waiting for", 'wgpwpp').'</strong>';
        $message .= ' <a href="' . $this->service_data['pending_order']['orderpay_url'] . '" target="_blank">' . __("payment", 'wgpwpp') . '</a>';
        $this->order_notice = $this->plugin->admin_section->notices->warning($message);
      }
    }

	}


	protected function get_step(): int {
		return 5;
	}
}