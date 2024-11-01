<?php

use Latte\Runtime as LR;

/** source: /data/web/virtuals/339638/virtual/www/domains/programmer-challenge.fun/wp-content/plugins/wgpwpp//admin/partials/wp-wgp/src/html/4.latte */
final class Templatec2dcd98c76 extends Latte\Runtime\Template
{
	protected const BLOCKS = [
		['progressBarTitle' => 'blockProgressBarTitle', 'main' => 'blockMain', 'js' => 'blockJs'],
	];


	public function main(): array
	{
		extract($this->params);
		echo "\n";
		if ($this->getParentName()) {
			return get_defined_vars();
		}
		$this->renderBlock('progressBarTitle', get_defined_vars()) /* line 3 */;
		echo '

';
		$this->renderBlock('main', get_defined_vars()) /* line 5 */;
		echo '

';
		$this->renderBlock('js', get_defined_vars()) /* line 135 */;
		echo "\n";
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['record' => '72, 102'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		$this->parentName = 'service_layout.latte';
		
	}


	/** {block progressBarTitle} on line 3 */
	public function blockProgressBarTitle(array $ʟ_args): void
	{
		echo LR\Filters::escapeHtmlText(__("Finishing setting up the service", 'wgpwpp')) /* line 3 */;
		
	}


	/** {block main} on line 5 */
	public function blockMain(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		echo '<img class="wds_image" src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($img_url)) /* line 6 */;
		echo '/ssl-certificate.png" alt="" srcset="">
<h1>';
		echo LR\Filters::escapeHtmlText(__("Finishing setting up the service", 'wgpwpp')) /* line 7 */;
		echo '</h1>

';
		if (!$plugin->get_client()->is_registered()) /* line 9 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 10 */;
			echo '    <p>';
			echo sprintf(__("The plugin has not been yet paired with your customer account at %s", 'wgpwpp'), '<strong>WEDOS&nbsp;Internet,&nbsp;a.s.</strong>') /* line 11 */;
			echo '</p>
    <p>';
			echo sprintf(__("Return back to the first step and register the plugin.", 'wgpwpp'), '<strong>WEDOS&nbsp;Internet,&nbsp;a.s.</strong>') /* line 12 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 14 */;
			echo '</button>
    </section>
';
		} elseif (!$service->is_created()) /* line 16 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 17 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__("The WEDOS Global service has not been yet completelly set up. Please return back to the second step and finish service set up process.", 'wgpwpp')) /* line 18 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 20 */;
			echo '</button>
    </section>
';
		} elseif (!$service->is_verified()) /* line 22 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 23 */;
			echo '    <p>';
			echo sprintf(__("Ownership of the domain %s has not been successfully verified based on TXT DNS record in domain`s DNS zone.", 'wgpwpp'), "<strong>{$plugin->get_host()}</strong>") /* line 24 */;
			echo '</p>
    <p>';
			echo LR\Filters::escapeHtmlText(__("Return back to the third step and verify domain`s ownership.", 'wgpwpp')) /* line 25 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 27 */;
			echo '</button>
    </section>
';
		} elseif ($service->is_pending_ns()) /* line 29 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 30 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__('Domain`s DNS configuration is not valid. Return to the fourth step and finish domain`s DNS configuration.', 'wgpwpp')) /* line 31 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 33 */;
			echo '</button>
    </section>
';
		} elseif ($service->is_pending_crt()) /* line 35 */ {
			$this->renderBlock('loader', [], 'html') /* line 36 */;
			echo '    <p>';
			echo sprintf(__('Generating TLS certificate for encrypted data transfer. %s', 'wgpwpp'),'<strong>'.__('This may take few minutes. Please be patient.', 'wgpwpp').'</strong>') /* line 37 */;
			echo '</p>
    <p>';
			echo LR\Filters::escapeHtmlText(sprintf(__("This page is regulary refreshed at %s seconds interval.", 'wgpwpp'), 60)) /* line 38 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 40 */;
			echo '</button>
        <button id="check" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Check status', 'wgpwpp')) /* line 41 */;
			echo '</button>
    </section>
';
		} elseif ($service->is_error_crt()) /* line 43 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 44 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__('An error occured while generating TLS certificate for encrypted data transfer.', 'wgpwpp')) /* line 45 */;
			echo '</p>
    <p>';
			echo LR\Filters::escapeHtmlText(__('To resolve the situation you can repeate request for TLS certificate generation, or go to the WEDOS Global Dashboard for more information.', 'wgpwpp')) /* line 46 */;
			echo '</p>
    <section id="control">
        <button id="retry_state" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Repeate request for TLS certificate generation', 'wgpwpp')) /* line 48 */;
			echo '</button>
        <button id="admin" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__("Go to the Dashboard", 'wgpwpp')) /* line 49 */;
			echo '</button>
    </section>
    <p></p>
    <p></p>
    <p></p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 55 */;
			echo '</button>
        <button id="check" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Check status', 'wgpwpp')) /* line 56 */;
			echo '</button>
    </section>
';
		} elseif (!$service->is_pointing_to_proxy()) /* line 58 */ {
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__('Everything is now ready to protect your website. There is only one thing left to do.', 'wgpwpp')) /* line 59 */;
			echo '</p>
    <p><strong>';
			echo LR\Filters::escapeHtmlText(__('The last thing we need is to direct your domain to our proxy servers by setting DNS records mentioned in the table below.', 'wgpwpp')) /* line 60 */;
			echo '</strong></p>

    <table id="table-dns">
        <caption>We recommend using the desktop to check DNS records.</caption>
        <thead>
        <tr>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('type', 'wgpwpp')) /* line 66 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('name', 'wgpwpp')) /* line 67 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('data', 'wgpwpp')) /* line 68 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('TTL', 'wgpwpp')) /* line 69 */;
			echo '</th>
        </tr>
        </thead>
        <tbody>
';
			$iterations = 0;
			foreach ($dns_records_new as $record) /* line 72 */ {
				echo '        <tr>
            <td data-label="type">';
				echo LR\Filters::escapeHtmlText($record['type']) /* line 74 */;
				echo '</td>
            <td data-label="name">';
				echo LR\Filters::escapeHtmlText($record['name']) /* line 75 */;
				echo '.';
				echo LR\Filters::escapeHtmlText($service->get_service_name()) /* line 75 */;
				echo '</td>
            <td class="wgpwpp-copy-to_clipboard-wrapper" title="';
				echo LR\Filters::escapeHtmlAttr(__('Click to copy', 'wgpwpp')) /* line 76 */;
				echo '" onclick="wgpwpp_copy_to_clipboard(this, this.firstElementChild);" data-label="data">';
				echo LR\Filters::escapeHtmlText($record['data']) /* line 76 */;
				echo '</td>
            <td data-label="TTL">';
				echo LR\Filters::escapeHtmlText($record['ttl']) /* line 77 */;
				echo '</td>
        </tr>
';
				$iterations++;
			}
			echo '        </tbody>
    </table>


';
			if (!$service->is_dns_approved()) /* line 83 */ {
				echo '    <p>';
				echo LR\Filters::escapeHtmlText(__('You can use the link below to grant consent to change DNS records automatically by us or you can do it manually by editing DNS records for your domain.', 'wgpwpp')) /* line 84 */;
				echo '</p>
    <p><strong><a style="font-weight: bold;" href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($dns_approval_url)) /* line 85 */;
				echo '" target="_blank">';
				echo LR\Filters::escapeHtmlText(sprintf(__('Use this link to give consent with automatic DNS setting of your domain %s', 'wgpwpp'), $service->get_service_name())) /* line 85 */;
				echo '</a></strong></p>
    <p><strong>';
				echo LR\Filters::escapeHtmlText(__('or', 'wgpwpp')) /* line 86 */;
				echo '</strong></p>
';
			}
			echo '
    <p><strong><a style="font-weight: bold;" href="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($dns_admin_link)) /* line 89 */;
			echo '" target="_blank">';
			echo LR\Filters::escapeHtmlText(sprintf(__('Use this link to be redirected to your %s domain`s DNS records administration page to set up the above DNS records.', 'wgpwpp'), $service->get_service_name())) /* line 89 */;
			echo '</a></strong></p>

    ';
			echo LR\Filters::escapeHtmlText($dns_warning->render()) /* line 91 */;
			echo '

    <table id="table-dns">
        <thead>
        <tr>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('type', 'wgpwpp')) /* line 96 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('name', 'wgpwpp')) /* line 97 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('data', 'wgpwpp')) /* line 98 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('TTL', 'wgpwpp')) /* line 99 */;
			echo '</th>
        </tr>
        </thead>
        <tbody>
';
			$iterations = 0;
			foreach ($dns_records as $record) /* line 102 */ {
				echo '        <tr>
            <td data-label="type">';
				echo LR\Filters::escapeHtmlText($record['type']) /* line 104 */;
				echo '</td>
            <td data-label="name">';
				echo LR\Filters::escapeHtmlText($record['name']) /* line 105 */;
				echo '.';
				echo LR\Filters::escapeHtmlText($service->get_service_name()) /* line 105 */;
				echo '</td>
            <td data-label="data">';
				echo LR\Filters::escapeHtmlText($record['data']) /* line 106 */;
				echo '</td>
            <td data-label="TTL">';
				echo LR\Filters::escapeHtmlText($record['ttl']) /* line 107 */;
				echo '</td>
        </tr>
';
				$iterations++;
			}
			echo '        </tbody>
    </table>

    <p>';
			echo LR\Filters::escapeHtmlText(sprintf(__("This page is regulary refreshed at %s seconds interval.", 'wgpwpp'), 60)) /* line 112 */;
			echo '</p>

    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 115 */;
			echo '</button>
        <button id="check" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Check status', 'wgpwpp')) /* line 116 */;
			echo '</button>
    </section>
';
		} elseif ($error) /* line 118 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 119 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__("An error occured during service setup proccess. Please try again.", 'wgpwpp')) /* line 120 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 122 */;
			echo '</button>
        <button id="repeat" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Repeat request', 'wgpwpp')) /* line 123 */;
			echo '</button>
    </section>
';
		} else /* line 125 */ {
			$this->renderBlock('doneSvg', [], 'html') /* line 126 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__("Everething is done. Your website is now under our protection.", 'wgpwpp')) /* line 127 */;
			echo '</p>
    <section id="control">
        <button id="next" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Finish', 'wgpwpp')) /* line 129 */;
			echo '</button>
    </section>
';
		}
		echo "\n";
	}


	/** {block js} on line 135 */
	public function blockJs(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->renderBlockParent('js', get_defined_vars()) /* line 136 */;
		echo '
    <script>
      function wgpwpp_service_retry_state(btn) {
          if (btn) {
              btn.classList.remove(\'btn-next\');
              btn.disabled = true;
          }

          let data = new FormData();
          data.append(\'action\', \'wgpwpp_layout_service_retry_state\');
          data.append(\'_ajax_nonce\', ';
		echo LR\Filters::escapeJs($nonce_service_retry_state) /* line 147 */;
		echo ');
          data.append(\'wgpwpp_step\', ';
		echo LR\Filters::escapeJs($step) /* line 148 */;
		echo ');

          fetch(';
		echo LR\Filters::escapeJs($ajax_url) /* line 150 */;
		echo ', {
              method: \'POST\',
              body: data
          })
              .then((response) => response.json())
              .then((data) => {
                  window.location.replace(data.redirect_uri);
              })
              .catch((e) => {
                  window.alert(__(\'Invalid request!\', \'wgpwpp\'));
              });
      }

      let wgp_adm_btn = document.querySelector("button#admin");
      if (wgp_adm_btn)
      {
          wgp_adm_btn.addEventListener("click", (event) => {
              window.open(';
		echo LR\Filters::escapeJs($wgp_url) /* line 167 */;
		echo ', \'_blank\');
          }, false);
      }

      let wgpwpp_button_retry_state = document.querySelector("button#retry_state");
      if (wgpwpp_button_retry_state)
      {
          wgpwpp_button_retry_state.addEventListener("click", (event) => {
              wgpwpp_button_retry_state.classList.remove(\'btn-next\');
              wgpwpp_button_retry_state.disabled = true;

              let url = new URL(window.location.href);
              url.searchParams.delete(\'service_error\');
              wgpwpp_service_retry_state(wgpwpp_button_retry_state);
          }, false);
      }

      let wgpwpp_button_repeat = document.querySelector("button#repeat");
      if (wgpwpp_button_repeat)
      {
          wgpwpp_button_repeat.addEventListener("click", (event) => {
              wgpwpp_button_repeat.classList.remove(\'btn-next\');
              wgpwpp_button_repeat.disabled = true;

              let url = new URL(window.location.href);
              url.searchParams.delete(\'service_error\');
              wgpwpp_service_info(wgpwpp_button_repeat);
          }, false);
      }

      let wgpwpp_button_check = document.querySelector("button#check");
      if (wgpwpp_button_check)
      {
        wgpwpp_button_check.addEventListener("click", (event) => {
          wgpwpp_service_info(wgpwpp_button_check);
        }, false);
      }

';
		if ($service->is_pending_crt() || (!$service->is_pointing_to_proxy() && !$service->is_error_crt())) /* line 205 */ {
			echo '        setTimeout(function() { wgpwpp_service_info(wgpwpp_button_check); }, 60000);
';
		}
		echo '    </script>
';
	}

}
