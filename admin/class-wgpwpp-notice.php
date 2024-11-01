<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - definition of notice
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Notice
{
	const TYPE_SUCCESS = 'wgpwpp-success';
	const TYPE_INFO = 'wgpwpp-info';
	const TYPE_WARN = 'wgpwpp-warn';
	const TYPE_ERROR = 'wgpwpp-error';
	const TYPE_CRITICAL = 'wgpwpp-critical';

	/**
	 * Plugin instance
	 *
	 * @since 1.0.0
	 * @var Wgpwpp
	 */
	private Wgpwpp $plugin;

	/**
	 * Notices message
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $message;

	/**
	 * Notices type (self::TYPE_*)
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $type;

	/**
	 * Flash notice
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private bool $flash;


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param Wgpwpp $plugin plugin instance
	 */
	public function __construct(Wgpwpp $plugin)
	{
		$this->plugin = $plugin;
	}


	/**
	 * Returns notices message
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_message(): string
	{
		return $this->message;
	}


	/**
	 * Returns notices type
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_type(): string
	{
		return $this->type;
	}


	/**
	 * Sets notices message
	 *
	 * @since 1.0.0
	 * @param string $message message
	 * @return Wgpwpp_Notice
	 */
	public function message(string $message): Wgpwpp_Notice
	{
		$this->message = $message;
		return $this;
	}


	/**
	 * Sets type "success"
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Notice
	 */
	public function success(): Wgpwpp_Notice
	{
		$this->type = self::TYPE_SUCCESS;
		return $this;
	}


	/**
	 * Sets type "info"
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Notice
	 */
	public function info(): Wgpwpp_Notice
	{
		$this->type = self::TYPE_INFO;
		return $this;
	}


	/**
	 * Sets type "warn"
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Notice
	 */
	public function warn(): Wgpwpp_Notice
	{
		$this->type = self::TYPE_WARN;
		return $this;
	}


	/**
	 * Sets type "error:
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Notice
	 */
	public function error(): Wgpwpp_Notice
	{
		$this->type = self::TYPE_ERROR;
		return $this;
	}


	/**
	 * Sets type "critical"
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Notice
	 */
	public function critical(): Wgpwpp_Notice
	{
		$this->type = self::TYPE_CRITICAL;
		return $this;
	}


	/**
	 * Sets as flash notice
	 *
	 * @since 1.0.0
	 * @param bool $on
	 * @return Wgpwpp_Notice
	 */
	public function flash(bool $on = true): Wgpwpp_Notice
	{
		$this->flash = $on;
		return $this;
	}


	/**
	 * Returns string representation of notice
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function __toString(): string
	{
		return serialize([
			'type'      => $this->type,
			'message'   => $this->message,
			'flash'     => $this->flash,
		]);
	}


	/**
	 * Checks if is flash notice
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_flash(): bool
	{
		return $this->flash;
	}


	/**
	 * Returns instance of notice from serialized string
	 *
	 * @since 1.0.0
	 * @param string $serialized serialized notice
	 * @return Wgpwpp_Notice
	 */
	public function instance(string $serialized): Wgpwpp_Notice
	{
		$data = unserialize($serialized);
		$this->type = $data['type'];
		$this->message = $data['message'];
		$this->flash = $data['flash'];
		return $this;
	}


	/**
	 * Renders notice
	 *
	 * @since 1.0.0
   * @param bool $return returns HTML
	 * @return void|string
	 */
	public function render(bool $dismiss = false, bool $return = false)
	{
		$params = new class($this->plugin, $this, $dismiss) extends Wgpwpp_Service_Layout_Parameters_Global
		{
			public string $message;

			public string $type;

      public string $icon_code;

      public string $description;

      public bool $dismiss;

			public function __construct(Wgpwpp $plugin, Wgpwpp_Notice $notice, bool $dismiss)
			{
				parent::__construct($plugin);
				$this->message = $notice->get_message();
				$this->type = $notice->get_type();
        $this->dismiss = $dismiss;

        switch ($this->type)
        {
          case $notice::TYPE_SUCCESS:
            $this->icon_code = 'fa-circle-check';
            $this->description = __('Success!', 'wgpwpp');
            break;

          case $notice::TYPE_INFO:
            $this->icon_code = 'fa-circle-info';
            $this->description = __('Info!', 'wgpwpp');
            break;

          case $notice::TYPE_WARN:
            $this->icon_code = 'fa-triangle-exclamation';
            $this->description = __('Warning!', 'wgpwpp');
            break;

          case $notice::TYPE_ERROR:
          case $notice::TYPE_CRITICAL:
            $this->icon_code = 'fa-circle-exclamation';
            $this->description = __('Error!', 'wgpwpp');
            break;

          default:
            $this->icon_code = '';
            $this->description = '';
        }
			}

			protected function get_step(): int {
				return 0;
			}
		};

    $function = $return ? 'renderToString' : 'render';

		return $this->plugin->admin_section->layout->latte->$function(
			'notice.latte',
			new $params($this->plugin, $this, $dismiss)
		);
	}
}