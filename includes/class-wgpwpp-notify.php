<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Class - class responsible for sending notifications to WordPress Administrator
 *
 * @since      1.1.0
 * @package    Wgpwpp
 * @subpackage Wgpwpp/includes
 * @author     František Hrubeš, WEDOS Internet, a.s. <frantisek.hrubes@wedos.com>
 */
class Wgpwpp_Notify extends Wgpwpp_REST_API
{
  /**
   * Name of scope for this REST API Endpoint authorization
   *
   * @since 1.1.0
   */
  const SCOPE = 'wgp_wp_notify';


  /**
   * Constructor
   *
   * @since 1.1.0
   * @param Wgpwpp $plugin
   * @return void
   */
  public function __construct(Wgpwpp $plugin)
  {
    parent::__construct($plugin);
    $this->define_hooks();
  }


  /**
   * Register all of the hooks related to the notification of WP Administrator.
   *
   * @since    1.1.0
   * @access   private
   */
  private function define_hooks()
  {
    $this->plugin->get_loader()->add_action('rest_api_init', $this, 'register_endpoint');
  }


  /**
   * Registers HTTP REST API endpoints for notification of WP Admin
   *
   * @since 1.1.0
   * @return void
   */
  public function register_endpoint()
  {
    register_rest_route( $this->plugin->get_plugin_name() . '/v1', '/notify', [
      'methods'             => 'POST',
      'callback'            => [ $this, 'process_notify_request' ],
      'permission_callback' => '__return_true'
    ]);
  }


  /**
   * Process request to notify WordPress Administrator
   *
   * @since 1.1.0
   * @param WP_REST_Request $request request object
   * @return array
   */
  public function process_notify_request(WP_REST_Request $request)
  {
    $error_msg = '';
    if (!$this->authorize_request($request, [self::SCOPE], $error_msg))
    {
      return [
        'code'  => 2000,
        'msg'   => "Unauthorized request! ".$error_msg,
      ];
    }

    $params = $request->get_body_params();

    // check if category is allowed
    $category = $params['category'] ?? '';

    $user_id = $params['user_id'] ?? NULL;
    if ($user_id === 0)
      $user_id = NULL;

    $lang = $params['lang'] ?? '';

    // email subject
    $subject = $params['subject'] ?? '';
    if (!$subject)
    {
      return [
        'code'  => 2001,
        'msg'   => "Missing message subject!",
      ];
    }

    // email body
    $body = $params['body'] ?? '';
    if (!$body)
    {
      return [
        'code'  => 2002,
        'msg'   => "Missing message body!",
      ];
    }

    $user_emails = [];

    $success = [];
    $errors = [];

    if ($category)
    {
      $user_ids = $this->plugin->admin_section->reports->get_allowed_users($category, $lang);
      if (!count($user_ids))
        return ['code' => 2003, 'msg' => 'No allowed user for required category: '.$category.' '.$lang];

      foreach ($user_ids as $uid)
      {
        $user = get_userdata($uid);
        if (!$user)
        {
          $errors[$uid] = 'Invalid User ID';
          continue;
        }

        if (!$user->user_email)
        {
          $errors[$uid] = 'Invalid User E-mail';
          continue;
        }

        $user_emails[$uid] = $user->user_email;
      }
    }
    else
    {
      $user_emails['blog_admin'] = $this->plugin->get_blog_admin_email();
    }

    if (!count($user_emails))
    {
      return [
        'code'  => 2004,
        'msg'   => 'No valid user email found.',
        'data'  => $errors,
      ];
    }

    // send email
    foreach ($user_emails as $uid => $email)
    {
      $mail = wp_mail($email, $subject, $body);
      if (!$mail)
      {
        $errors[$uid] = 'Failed to send email to WordPress Administrator`s email address: '.$email;
        continue;
      }

      $success[$uid] = $uid;
    }

    if (!count($success))
    {
      return [
        'code'  => 2005,
        'msg'   => 'No notification sent.',
        'data'  => $errors,
      ];
    }

    return [
      'code'  => 1000,
      'msg'   => "Email successfully sent to WordPress Administrator`s mailbox.",
      'data'  => [
        'success_user_ids'  => $success,
        'errors'  => $errors,
      ],
    ];
  }
}