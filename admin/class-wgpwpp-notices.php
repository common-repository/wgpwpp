<?php

if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for admin notices
 *
 * @since      1.0.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/admin
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Notices
{
	/**
	 * Plugin instance
	 *
	 * @since 1.0.0
	 * @var Wgpwpp
	 */
	private Wgpwpp $plugin;

	/**
	 * Notices
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $notices;


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param Wgpwpp $plugin plugin instance
	 */
	public function __construct(Wgpwpp $plugin)
	{
		$this->plugin = $plugin;
		$this->notices = [];
	}


	/**
	 * Set error notice
	 *
	 * @since 1.0.0
	 * @param string $message notice message
	 * @param bool $flash set notice to render after redirection
	 * @return Wgpwpp_Notice
	 */
	public function error(string $message, bool $flash = false): Wgpwpp_Notice
	{
		$notice = $this->get_notice()->error()->message($message)->flash($flash);
		$this->set_notice($notice);
		return $notice;
	}


	/**
	 * Set warning notice
	 *
	 * @since 1.0.0
	 * @param string $message notice message
	 * @param bool $flash set notice to render after redirection
	 * @return Wgpwpp_Notice
	 */
	public function warning(string $message, bool $flash = false): Wgpwpp_Notice
	{
		$notice = $this->get_notice()->warn()->message($message)->flash($flash);
		$this->set_notice($notice);
		return $notice;
	}


	/**
	 * Set success notice
	 *
	 * @since 1.0.0
	 * @param string $message notice message
	 * @param bool $flash set notice to render after redirection
	 * @return Wgpwpp_Notice
	 */
	public function success(string $message, bool $flash = false): Wgpwpp_Notice
	{
		$notice = $this->get_notice()->success()->message($message)->flash($flash);
		$this->set_notice($notice);
		return $notice;
	}


	/**
	 * Set info notice
	 *
	 * @since 1.0.0
	 * @param string $message notice message
	 * @param bool $flash set notice to render after redirection
	 * @return Wgpwpp_Notice
	 */
	public function info(string $message, bool $flash = false): Wgpwpp_Notice
	{
		$notice = $this->get_notice()->info()->message($message)->flash($flash);
		$this->set_notice($notice);
		return $notice;
	}


	/**
	 * Set critical error notice
	 *
	 * @since 1.0.0
	 * @param string $message notice message
	 * @param bool $flash set notice to render after redirection
	 * @return Wgpwpp_Notice
	 */
	public function critical(string $message, bool $flash = false): Wgpwpp_Notice
	{
		$notice = $this->get_notice()->critical()->message($message)->flash($flash);
		$this->set_notice($notice);
		return $notice;
	}


	/**
	 * Returns instances of current notices
	 *
	 * @since 1.0.0
	 * @param bool $no_flash without flash notices
	 * @return array
	 */
	public function get_notices(bool $no_flash = false): array
	{
		$notices = $this->notices;

		if (!$no_flash)
			$notices = array_merge($notices, $this->get_flash_notices());

		return $notices;
	}


	/**
	 * Returns instances of currently set flash notices
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_flash_notices(): array
	{
		$flashes = get_option($this->plugin->get_plugin_name().'-flashes', []);

		if (!empty($flashes))
		{
			delete_option($this->plugin->get_plugin_name().'-flashes', []);

			array_walk($flashes, function(&$notice) {
				$notice = $this->get_notice()->instance($notice);
			});
		}

		return $flashes;
	}


	/**
	 * Set notice for rendering after redirection
	 *
	 * @since 1.0.0
	 * @param Wgpwpp_Notice $notice notice object
	 * @return void
	 */
	private function set_flash_notice(Wgpwpp_Notice $notice)
	{
		$flashes = get_option($this->plugin->get_plugin_name().'-flashes', []);

		$flashes[] = (string)$notice;

		update_option($this->plugin->get_plugin_name().'-flashes', $flashes);
	}



	/**
	 * Set notice
	 *
	 * @since 1.0.0
	 * @param Wgpwpp_Notice $notice notice instance
	 * @return void
	 */
	private function set_notice(Wgpwpp_Notice $notice)
	{
		if (!$notice->get_message() || !$notice->get_type())
			return;

		if ($notice->is_flash())
			$this->set_flash_notice($notice);
		else
			$this->notices[] = $notice;
	}


	/**
	 * Returns instance of new empty notice
	 *
	 * @since 1.0.0
	 * @return Wgpwpp_Notice
	 */
	private function get_notice(): Wgpwpp_Notice
	{
		return new Wgpwpp_Notice($this->plugin);
	}

}