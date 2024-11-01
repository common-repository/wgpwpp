<?php

use Latte\Runtime as LR;

/** source: /data/web/virtuals/339638/virtual/www/domains/programmer-challenge.fun/wp-content/plugins/wgpwpp//admin/partials/wp-wgp/src/html/5.latte */
final class Template1b2a066443 extends Latte\Runtime\Template
{
	protected const BLOCKS = [
		['progressBarTitle' => 'blockProgressBarTitle', 'contentId' => 'blockContentId', 'main' => 'blockMain', 'js' => 'blockJs'],
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
		$this->renderBlock('contentId', get_defined_vars()) /* line 5 */;
		echo '

';
		$this->renderBlock('main', get_defined_vars()) /* line 7 */;
		echo '

';
		$this->renderBlock('js', get_defined_vars()) /* line 77 */;
		echo "\n";
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['record' => '51'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		$this->parentName = 'service_layout.latte';
		
	}


	/** {block progressBarTitle} on line 3 */
	public function blockProgressBarTitle(array $ʟ_args): void
	{
		echo LR\Filters::escapeHtmlText(__("Congratulations", 'wgpwpp')) /* line 3 */;
		
	}


	/** {block contentId} on line 5 */
	public function blockContentId(array $ʟ_args): void
	{
		echo 'finish';
	}


	/** {block main} on line 7 */
	public function blockMain(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		echo '<img class="wds_image logo" src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($img_url)) /* line 8 */;
		echo 'logo.png" alt="www.flaticon.com/free-icons">
<h1><span>';
		echo LR\Filters::escapeHtmlText(__("Congratulations", 'wgpwpp')) /* line 9 */;
		echo '</span></h1>
<p>';
		echo sprintf(__("Your website on the domain %s is now under the protection of our service %s.", 'wgpwpp'), "<strong>{$service->get_service_name()}</strong>", "<strong>WEDOS Global</strong>") /* line 10 */;
		echo '</p>

';
		if ($service_data) /* line 12 */ {
			echo '    <table id="table-dns" class="wgpwpp-service-info-table">
        <thead>
        <tr>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('service name', 'wgpwpp')) /* line 16 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('service variant', 'wgpwpp')) /* line 17 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('service state', 'wgpwpp')) /* line 18 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('service expiration', 'wgpwpp')) /* line 19 */;
			echo '</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td data-label="service name">';
			echo LR\Filters::escapeHtmlText($service_data['name']) /* line 24 */;
			echo '</td>
                <td data-label="service variant">';
			echo LR\Filters::escapeHtmlText($service_data['variant']['name']) /* line 25 */;
			echo ' - ';
			echo LR\Filters::escapeHtmlText($service_data['variant']['desc']) /* line 25 */;
			if ($service_data['is_trial']) /* line 25 */ {
				echo ' (TRIAL)';
			}
			echo '</td>
                <td data-label="service state">';
			echo $service_state /* line 26 */;
			echo '</td>
                <td data-label="service expiration">';
			echo LR\Filters::escapeHtmlText($service_expiration) /* line 27 */;
			echo '</td>
            </tr>
';
			if ((isset($trial_notice) || isset($status_notice) || isset($order_notice))) /* line 29 */ {
				echo '            <tr>
                <td colspan="4">
';
				if (isset($trial_notice)) /* line 31 */ {
					echo '                    <div style="width:100%;">';
					echo LR\Filters::escapeHtmlText($trial_notice->render()) /* line 31 */;
					echo '</div>
';
				}
				if (isset($status_notice)) /* line 32 */ {
					echo '                    <div style="width:100%;">';
					echo LR\Filters::escapeHtmlText($status_notice->render()) /* line 32 */;
					echo '</div>
';
				}
				if (isset($order_notice)) /* line 33 */ {
					echo '                    <div style="width:100%;">';
					echo LR\Filters::escapeHtmlText($order_notice->render()) /* line 33 */;
					echo '</div>
';
				}
				echo '                </td>
            </tr>
';
			}
			echo '        </tbody>
    </table>
';
		}
		echo "\n";
		if ($proxy_ips) /* line 40 */ {
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__('Following IP addresses of proxy servers were assigned for your domain to be protected.', 'wgpwpp')) /* line 41 */;
			echo '</p>
    <table id="table-dns">
        <thead>
        <tr>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('type', 'wgpwpp')) /* line 45 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('data', 'wgpwpp')) /* line 46 */;
			echo '</th>
            <th scope="col">';
			echo LR\Filters::escapeHtmlText(__('TTL', 'wgpwpp')) /* line 47 */;
			echo '</th>
        </tr>
        </thead>
        <tbody>
';
			$iterations = 0;
			foreach ($proxy_ips as $record) /* line 51 */ {
				echo '            <tr>
                <td data-label="type">';
				echo LR\Filters::escapeHtmlText(count(explode('.', $record)) === 4 ? 'A' : 'AAAA') /* line 52 */;
				echo '</td>
                <td data-label="data">';
				echo LR\Filters::escapeHtmlText($record) /* line 53 */;
				echo '</td>
                <td data-label="TTL">300</td>
            </tr>
';
				$iterations++;
			}
			echo '            <tr>
                <td colspan="3">
                    <div style="width:100%;">
';
			$notice = $plugin->admin_section->notices->info(__('In the table you can see IP addresses of our proxy servers assigned for your domain. These IP addresess were set up in previous step. We show it here only for your information if you need to check domains DNS setting.', 'wgpwpp')) /* line 59 */;
			echo '                        ';
			echo LR\Filters::escapeHtmlText($notice->render()) /* line 60 */;
			echo '
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
';
		}
		echo '
<h2>';
		echo LR\Filters::escapeHtmlText(__("We wish our product to help you achieve your goals.", 'wgpwpp')) /* line 68 */;
		echo '</h2>
<section id="control">
    <button id="admin" class="btn btn-next">';
		echo LR\Filters::escapeHtmlText(__("Go to the Dashboard", 'wgpwpp')) /* line 70 */;
		echo '</button>
</section>
<section id="control">
    <button id="check" class="btn btn-back">';
		echo LR\Filters::escapeHtmlText(__("Check service status", 'wgpwpp')) /* line 73 */;
		echo '</button>
</section>
';
	}


	/** {block js} on line 77 */
	public function blockJs(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->renderBlockParent('js', get_defined_vars()) /* line 78 */;
		echo '<script>
    let wgp_adm_btn = document.querySelector("button#admin");
    if (wgp_adm_btn)
    {
        wgp_adm_btn.addEventListener("click", (event) => {
            window.open(';
		echo LR\Filters::escapeJs($wgp_url) /* line 84 */;
		echo ', \'_blank\');
        }, false);
    }

    let wgpwpp_button_check = document.querySelector("button#check");
    if (wgpwpp_button_check)
    {
      wgpwpp_button_check.addEventListener("click", (event) => {
        wgpwpp_service_info(wgpwpp_button_check);
      }, false);
    }
</script>
';
	}

}
