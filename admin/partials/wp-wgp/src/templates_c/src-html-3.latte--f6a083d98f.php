<?php

use Latte\Runtime as LR;

/** source: /data/web/virtuals/339638/virtual/www/domains/programmer-challenge.fun/wp-content/plugins/wgpwpp//admin/partials/wp-wgp/src/html/3.latte */
final class Templatef6a083d98f extends Latte\Runtime\Template
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
		$this->renderBlock('js', get_defined_vars()) /* line 109 */;
		echo "\n";
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['ns' => '79'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		$this->parentName = 'service_layout.latte';
		
	}


	/** {block progressBarTitle} on line 3 */
	public function blockProgressBarTitle(array $ʟ_args): void
	{
		echo LR\Filters::escapeHtmlText(__("DNS setting", 'wgpwpp')) /* line 3 */;
		
	}


	/** {block main} on line 5 */
	public function blockMain(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		echo '<img class="wds_image" src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($img_url)) /* line 6 */;
		echo '/dns.png" alt="www.flaticon.com/free-icons/" srcset="">
<h1>';
		echo LR\Filters::escapeHtmlText(__("DNS setting", 'wgpwpp')) /* line 7 */;
		echo '</h1>
';
		if (!$plugin->get_client()->is_registered()) /* line 8 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 9 */;
			echo '    <p>';
			echo sprintf(__("The plugin has not been yet paired with your customer account at %s", 'wgpwpp'), '<strong>WEDOS&nbsp;Internet,&nbsp;a.s.</strong>') /* line 10 */;
			echo '</p>
    <p>';
			echo sprintf(__("Return back to the first step and register the plugin.", 'wgpwpp'), '<strong>WEDOS&nbsp;Internet,&nbsp;a.s.</strong>') /* line 11 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 13 */;
			echo '</button>
    </section>
';
		} elseif (!$service->is_created()) /* line 15 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 16 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__("The WEDOS Global service has not been yet completelly set up. Please return back to the second step and finish service set up process.", 'wgpwpp')) /* line 17 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 19 */;
			echo '</button>
    </section>
';
		} elseif ($error) /* line 21 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 22 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__("An error occured during service setup proccess. Please try again.", 'wgpwpp')) /* line 23 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 25 */;
			echo '</button>
        <button id="repeat" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Repeat request', 'wgpwpp')) /* line 26 */;
			echo '</button>
    </section>
';
		} elseif ($service->is_stucked()) /* line 28 */ {
			echo '    <p>';
			echo sprintf(__("%s service is now preparing to be set up for your domain %s.", 'wgpwpp'), "<strong>WEDOS Global</strong>", "<strong>{$plugin->get_host()}</strong>") /* line 29 */;
			echo '</p>
    <p>';
			echo LR\Filters::escapeHtmlText(__("This action may take few minutes. Be patient please.", 'wgpwpp')) /* line 30 */;
			echo '</p>
';
			$this->renderBlock('loader', [], 'html') /* line 31 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(sprintf(__("This page is regulary refreshed at %s seconds interval.", 'wgpwpp'), 10)) /* line 32 */;
			echo '</p>

    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 35 */;
			echo '</button>
        <button id="check" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Check status', 'wgpwpp')) /* line 36 */;
			echo '</button>
    </section>
';
		} elseif (!$service->is_verified()) /* line 38 */ {
			echo '    <p>';
			echo sprintf(__("We have to verify you are owner of the domain %s before we can continue with plugin setting.", 'wgpwpp'), "<strong>{$plugin->get_host()}</strong>") /* line 39 */;
			echo '</p>
    <p>';
			echo sprintf(__("Domain ownership verification is based on DNS TXT record verification. %s", 'wgpwpp'), "<strong>".__('Ask the DNS server provider for your domain to set up the following TXT record:', 'wgpwpp')."</strong>") /* line 40 */;
			echo '</p>

    <table id="table-dns">
        <caption>We recommend using the desktop to check DNS records.</caption>
        <thead>
        <tr>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('type', 'wgpwpp')) /* line 46 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('name', 'wgpwpp')) /* line 47 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('data', 'wgpwpp')) /* line 48 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('TTL', 'wgpwpp')) /* line 49 */;
			echo '</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td data-label="type">TXT</td>
            <td data-label="name" class="wgpwpp-copy-to_clipboard-wrapper bg-center-right" title="';
			echo LR\Filters::escapeHtmlAttr(__('Click to copy', 'wgpwpp')) /* line 55 */;
			echo '" onclick="wgpwpp_copy_to_clipboard(this);">';
			echo LR\Filters::escapeHtmlText($txt_data['name']) /* line 55 */;
			echo '</td>
            <td data-label="data" class="wgpwpp-copy-to_clipboard-wrapper bg-center-right" title="';
			echo LR\Filters::escapeHtmlAttr(__('Click to copy', 'wgpwpp')) /* line 56 */;
			echo '" onclick="wgpwpp_copy_to_clipboard(this);">';
			echo LR\Filters::escapeHtmlText($txt_data['value']) /* line 56 */;
			echo '</td>
            <td data-label="TTL">300</td>
        </tr>
        </tbody>
    </table>

';
			$this->renderBlock('loader', [], 'html') /* line 62 */;
			echo '
    ';
			echo LR\Filters::escapeHtmlText($plugin->admin_section->notices->info(__('Note that the TXT record setting will take effect within one hour.', 'wgpwpp'))->render()) /* line 64 */;
			echo '

    <p>';
			echo LR\Filters::escapeHtmlText(sprintf(__("This page is regulary refreshed at %s minutes interval.", 'wgpwpp'), 10)) /* line 66 */;
			echo '</p>

    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 69 */;
			echo '</button>
        <button id="check" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Check status', 'wgpwpp')) /* line 70 */;
			echo '</button>
    </section>
';
		} elseif ($service->is_pending_ns()) /* line 72 */ {
			echo '
    <p>';
			echo sprintf(__("The %s service is now ready to protect your domain. To be protected the domain must use our DNS servers that are under our protection.", 'wgpwpp'), "<strong>WEDOS Global</strong>") /* line 74 */;
			echo '</p>
    <p>';
			echo LR\Filters::escapeHtmlText(__("Our DNS servers are prepared to serve DNS records for your domain. DNS records were copied from currently assigned DNS servers to the domain.", 'wgpwpp')) /* line 75 */;
			echo '</p>
    <p>';
			echo sprintf(__("Ask the registrar of domain %s to assign following DNS servers to the domain. Or you can do it yourself in registrar`s admin panel if there is this option available. %s", 'wgpwpp'), "<strong>{$plugin->get_host()}</strong>", "<strong>".__('All 4 DNS servers must be set up.', 'wgpwpp')."</strong>") /* line 76 */;
			echo '</p>
    
    <div id="table-ns" style="width:auto;">
        <div>
';
			$iterations = 0;
			foreach ($ns_data as $ns) /* line 79 */ {
				echo '            <div class="wgpwpp-copy-to_clipboard-wrapper-bg wds-table-ns" title="';
				echo LR\Filters::escapeHtmlAttr(__('Click to copy', 'wgpwpp')) /* line 80 */;
				echo '" onclick="wgpwpp_copy_to_clipboard(this, this.firstElementChild);">
                <div>';
				echo LR\Filters::escapeHtmlText($ns) /* line 81 */;
				echo '</div>
            </div>
';
				$iterations++;
			}
			echo '        </div>
    </div>

    <p><strong>';
			echo LR\Filters::escapeHtmlText(__("You can continue as soon as we are able to resolve mentioned nameservers by the domain.", 'wgpwpp')) /* line 86 */;
			echo '</strong></p>
';
			$this->renderBlock('loader', [], 'html') /* line 87 */;
			echo '    ';
			echo LR\Filters::escapeHtmlText($plugin->admin_section->notices->info(__('Note that it may take up to several hours for the change of DNS servers to take effect.', 'wgpwpp'))->render()) /* line 88 */;
			echo '
    <p>';
			echo LR\Filters::escapeHtmlText(sprintf(__("This page is regulary refreshed at %s minutes interval.", 'wgpwpp'), 10)) /* line 89 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 91 */;
			echo '</button>
        <button id="check" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Check status', 'wgpwpp')) /* line 92 */;
			echo '</button>
    </section>

';
		} else /* line 95 */ {
			echo '
    <p>';
			echo LR\Filters::escapeHtmlText(__("Domain nameservers are properly configured.", 'wgpwpp')) /* line 97 */;
			echo '</p>
    <p>';
			echo LR\Filters::escapeHtmlText(__("Now you can proceed with the next steps of setting up the plugin.", 'wgpwpp')) /* line 98 */;
			echo '</p>
';
			$this->renderBlock('doneSvg', [], 'html') /* line 99 */;
			echo '    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 101 */;
			echo '</button>
        <button id="next" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Next step', 'wgpwpp')) /* line 102 */;
			echo '</button>
    </section>

';
		}
		echo "\n";
	}


	/** {block js} on line 109 */
	public function blockJs(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->renderBlockParent('js', get_defined_vars()) /* line 110 */;
		echo '
    <script>

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
		if (($service->is_pending_ns() || $service->is_stucked() || !$service->is_verified()) && !$error) /* line 135 */ {
			echo '          setTimeout(function() {
            wgpwpp_service_info(wgpwpp_button_check);
          }, ';
			if ($service->is_stucked()) /* line 138 */ {
				echo '10000';
			} else /* line 138 */ {
				echo '600000';
			}
			echo ');
';
		}
		echo '    </script>
';
	}

}
