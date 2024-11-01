<?php

use Latte\Runtime as LR;

/** source: /data/web/virtuals/339638/virtual/www/domains/programmer-challenge.fun/wp-content/plugins/wgpwpp//admin/partials/wp-wgp/src/html/2.latte */
final class Templatee03f74e866 extends Latte\Runtime\Template
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
		$this->renderBlock('js', get_defined_vars()) /* line 46 */;
		echo "\n";
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		$this->parentName = 'service_layout.latte';
		
	}


	/** {block progressBarTitle} on line 3 */
	public function blockProgressBarTitle(array $ʟ_args): void
	{
		echo LR\Filters::escapeHtmlText(__("Service setup", 'wgpwpp')) /* line 3 */;
		
	}


	/** {block main} on line 5 */
	public function blockMain(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		echo '<img class="wds_image" src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($img_url)) /* line 6 */;
		echo '/hourglass.png" alt="" srcset="">
<h1>';
		echo LR\Filters::escapeHtmlText(__("Service setup", 'wgpwpp')) /* line 7 */;
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
		} elseif ($service->is_created()) /* line 15 */ {
			echo '    <p>';
			echo sprintf(__("%s service was successfully created for your domain %s.", 'wgpwpp'), "<strong>WEDOS Global</strong>", "<strong>{$plugin->get_host()}</strong>") /* line 16 */;
			echo '</p>
    <p>';
			echo LR\Filters::escapeHtmlText(__("Now you can proceed with the next steps of setting up the plugin.", 'wgpwpp')) /* line 17 */;
			echo '</p>
';
			$this->renderBlock('doneSvg', [], 'html') /* line 18 */;
			echo '    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 20 */;
			echo '</button>
        <button id="next" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Next step', 'wgpwpp')) /* line 21 */;
			echo '</button>
    </section>
';
		} elseif ($error) /* line 23 */ {
			$this->renderBlock('errorSvg', [], 'html') /* line 24 */;
			echo '    <p>';
			echo LR\Filters::escapeHtmlText(__("An error occured during service setup proccess. Please try again.", 'wgpwpp')) /* line 25 */;
			echo '</p>
    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 27 */;
			echo '</button>
        <button id="repeat" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Repeat request', 'wgpwpp')) /* line 28 */;
			echo '</button>
    </section>
';
		} else /* line 30 */ {
			echo '    <p>';
			echo sprintf(__("%s service is now preparing to be set up for your domain %s.", 'wgpwpp'), "<strong>WEDOS Global</strong>", "<strong>{$plugin->get_host()}</strong>") /* line 31 */;
			echo '</p>
    <p>';
			echo sprintf(__("This step should take only few seconds. %s", 'wgpwpp'), " <strong>".__("Be patient please.", "wgpwpp")."</strong>") /* line 32 */;
			echo '</p>
';
			$this->renderBlock('loader', [], 'html') /* line 33 */;
			echo '
    <p>';
			echo LR\Filters::escapeHtmlText(sprintf(__("This page is regulary refreshed at %s seconds interval.", 'wgpwpp'), 10)) /* line 35 */;
			echo '</p>

    <section id="control">
        <button id="back" class="btn">';
			echo LR\Filters::escapeHtmlText(__('Previous step', 'wgpwpp')) /* line 38 */;
			echo '</button>
        <button id="check" class="btn btn-next">';
			echo LR\Filters::escapeHtmlText(__('Check status', 'wgpwpp')) /* line 39 */;
			echo '</button>
    </section>
';
		}
		echo "\n";
	}


	/** {block js} on line 46 */
	public function blockJs(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->renderBlockParent('js', get_defined_vars()) /* line 47 */;
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
                window.location.replace(url);
            }, false);
        }

        let wgpwpp_button_check = document.querySelector("button#check");
        if (wgpwpp_button_check)
        {
          wgpwpp_button_check.addEventListener("click", (event) => {
                wgpwpp_service_create();
            }, false);
        }

        function wgpwpp_service_create() {
            if (wgpwpp_button_check) {
              wgpwpp_button_check.classList.remove(\'btn-next\');
              wgpwpp_button_check.disabled = true;
            }

            let data = new FormData();
            data.append(\'action\', \'wgpwpp_layout_service_create\');
            data.append(\'_ajax_nonce\', ';
		echo LR\Filters::escapeJs($nonce_service) /* line 80 */;
		echo ');

            fetch(';
		echo LR\Filters::escapeJs($ajax_url) /* line 82 */;
		echo ', {
                method: \'POST\',
                body: data
            })
            .then((response) => response.json())
            .then((data) => {
                window.location.replace(data.redirect_uri);
            })
            .catch((e) => {
                window.alert(';
		echo LR\Filters::escapeJs(__('Failed to start service setup process.', 'wgpwpp')) /* line 91 */;
		echo ');
            })
        }

';
		if (!$service->is_created() && !$error) /* line 95 */ {
			echo '            setTimeout(wgpwpp_service_create, 10000)
';
		}
		echo '
    </script>
';
	}

}
