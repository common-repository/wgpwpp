<?php

use Latte\Runtime as LR;

/** source: /data/web/virtuals/339638/virtual/www/domains/programmer-challenge.fun/wp-content/plugins/wgpwpp//admin/partials/wp-wgp/src/html/service_layout.latte */
final class Template19a0950ab1 extends Latte\Runtime\Template
{
	protected const BLOCKS = [
		['contentId' => 'blockContentId', 'notices' => 'blockNotices', 'main' => 'blockMain', 'progress' => 'blockProgress', 'css' => 'blockCss', 'js' => 'blockJs'],
	];


	public function main(): array
	{
		extract($this->params);
		echo '
<div id="';
		if ($this->getParentName()) {
			return get_defined_vars();
		}
		$this->renderBlock('contentId', get_defined_vars(), function ($s, $type) {
			$ʟ_fi = new LR\FilterInfo($type);
			return LR\Filters::convertTo($ʟ_fi, 'htmlAttr', $s);
		}) /* line 3 */;
		echo '">

';
		$this->renderBlock('notices', get_defined_vars()) /* line 5 */;
		echo '

    <main>
        ';
		$this->renderBlock('main', get_defined_vars()) /* line 12 */;
		echo '
    </main>

    ';
		$this->renderBlock('progress', get_defined_vars()) /* line 15 */;
		echo '
</div>

';
		$this->renderBlock('css', get_defined_vars()) /* line 18 */;
		echo '

';
		$this->renderBlock('js', get_defined_vars()) /* line 22 */;
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['notice' => '6'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		$this->parentName = 'layout.latte';
		
	}


	/** {block contentId} on line 3 */
	public function blockContentId(array $ʟ_args): void
	{
		echo 'home';
	}


	/** {block notices} on line 5 */
	public function blockNotices(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		echo '    <div class="wgpwpp-notices">
';
		$iterations = 0;
		foreach ($notices as $notice) /* line 6 */ {
			echo '        ';
			echo LR\Filters::escapeHtmlText($notice->render()) /* line 7 */;
			echo "\n";
			$iterations++;
		}
		echo '    </div>
';
	}


	/** {block main} on line 12 */
	public function blockMain(array $ʟ_args): void
	{
		
	}


	/** {block progress} on line 15 */
	public function blockProgress(array $ʟ_args): void
	{
		
	}


	/** {block css} on line 18 */
	public function blockCss(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->renderBlockParent('css', get_defined_vars()) /* line 19 */;
		
	}


	/** {block js} on line 22 */
	public function blockJs(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->renderBlockParent('js', get_defined_vars()) /* line 23 */;
		echo '<script>
  let wgpwpp_button_back = document.querySelector("button#back");
  if (wgpwpp_button_back)
  {
    wgpwpp_button_back.addEventListener("click", (event) => {
      event.preventDefault();

      wgpwpp_button_back.disabled = true;

      let data = new FormData();
      data.append(\'action\', \'wgpwpp_layout_step\');
      data.append(\'wgpwpp_step\', ';
		echo LR\Filters::escapeJs($step - 1) /* line 35 */;
		echo ');
      data.append(\'_ajax_nonce\', ';
		echo LR\Filters::escapeJs($nonce_step) /* line 36 */;
		echo ');

      fetch(';
		echo LR\Filters::escapeJs($ajax_url) /* line 38 */;
		echo ', {
        method: \'POST\',
        body: data
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.result === \'error\') {
            window.location.replace(window.location + "&error");
          }
          else
          {
            window.location.replace(data.data.redirect_uri);
          }
        })
        .catch((e) => {
          window.alert(';
		echo LR\Filters::escapeJs(__('Failed to go to previous step!', 'wgpwpp')) /* line 53 */;
		echo ');
        })

    }, false);
  }

  let wgpwpp_button_next = document.querySelector("button#next");
  if (wgpwpp_button_next)
  {
    wgpwpp_button_next.addEventListener("click", (event) => {
      event.preventDefault();

      wgpwpp_button_next.classList.remove(\'btn-next\');
      wgpwpp_button_next.disabled = true;

      let data = new FormData();
      data.append(\'action\', \'wgpwpp_layout_step\');
      data.append(\'wgpwpp_step\', ';
		echo LR\Filters::escapeJs($next_step) /* line 70 */;
		echo ');
      data.append(\'_ajax_nonce\', ';
		echo LR\Filters::escapeJs($nonce_step) /* line 71 */;
		echo ');

      fetch(';
		echo LR\Filters::escapeJs($ajax_url) /* line 73 */;
		echo ', {
        method: \'POST\',
        body: data
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.result === \'error\') {
            window.location.replace(window.location + "&error");
          }
          else
          {
            window.location.replace(data.data.redirect_uri);
          }
        })
        .catch((e) => {
          window.alert(';
		echo LR\Filters::escapeJs(__('Failed to go to next step!', 'wgpwpp')) /* line 88 */;
		echo ');
        })

    }, false);
  }

  function wgpwpp_service_info(btn) {
      if (btn) {
        btn.classList.remove(\'btn-next\');
        btn.classList.remove(\'btn-back\');
        btn.disabled = true;
      }

      let data = new FormData();
      data.append(\'action\', \'wgpwpp_layout_service_info\');
      data.append(\'_ajax_nonce\', ';
		echo LR\Filters::escapeJs($nonce_service_info) /* line 103 */;
		echo ');
      data.append(\'wgpwpp_step\', ';
		echo LR\Filters::escapeJs($step) /* line 104 */;
		echo ');

      fetch(';
		echo LR\Filters::escapeJs($ajax_url) /* line 106 */;
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
</script>
';
	}

}
