<?php

use Latte\Runtime as LR;

/** source: /data/web/virtuals/339638/virtual/www/domains/programmer-challenge.fun/wp-content/plugins/wgpwpp//admin/partials/wp-wgp/src/html/notice.latte */
final class Templatef06db939ee extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="wgpwpp-notice ';
		echo LR\Filters::escapeHtmlAttr($type) /* line 1 */;
		echo '">';
		echo $message /* line 1 */;
		echo '</div>';
		return get_defined_vars();
	}

}
