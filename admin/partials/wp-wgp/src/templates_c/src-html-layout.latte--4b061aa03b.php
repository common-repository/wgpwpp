<?php

use Latte\Runtime as LR;

/** source: /data/web/virtuals/339638/virtual/www/domains/programmer-challenge.fun/wp-content/plugins/wgpwpp//admin/partials/wp-wgp/src/html/layout.latte */
final class Template4b061aa03b extends Latte\Runtime\Template
{
	protected const BLOCKS = [
		['contentId' => 'blockContentId', 'notices' => 'blockNotices', 'main' => 'blockMain', 'progress' => 'blockProgress', 'css' => 'blockCss', 'js' => 'blockJs', 'progressBarTitle' => 'blockProgressBarTitle', 'doneSvg' => 'blockDoneSvg', 'errorSvg' => 'blockErrorSvg', 'loader' => 'blockLoader'],
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
		}) /* line 2 */;
		echo '">

';
		$this->renderBlock('notices', get_defined_vars()) /* line 4 */;
		echo '

    <main>
        ';
		$this->renderBlock('main', get_defined_vars()) /* line 11 */;
		echo '
    </main>

    ';
		$this->renderBlock('progress', get_defined_vars()) /* line 14 */;
		echo '

    <div id="overlay">
      <div id="modal">
          <svg class="h-6 ml-auto fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
            <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"></path>
          </svg>
          <div>
            <h3>How does the plugin work?</h3>
            <p>This plugin <b>optimizes web performance and security</b>. Its main purpose is to load web pages faster, decrease server loads and protect against various online threats, such as DDoS attacks or unauthorized access. Install the plugin in your WordPress administration and change DNS servers according to instructions, so it can effectively monitor and filter website traffic.</p>
            <p>The plugin works on the anycast proxy principle, which directs traffic through its optimized and secure servers. These servers process and optimize content before it reaches users. The plugin also utilizes intelligent caching, compression and minification of files to further speed up rendering times and reduce server load. It also features a firewall, bot detection, DDoS protection and other tools to maintain a high level of security for your website and users.</p>
          </div>
      </div>
    </div>

    <aside id="faq">
        <div class="flag">
            <img src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($img_url)) /* line 31 */;
		echo 'rubber-ring.png" alt="">
            <h3>FAQ</h3>
        </div>
        <ul class="hidden">
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('What sort of company is WEDOS?', 'wgpwpp')) /* line 37 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('What sort of company is WEDOS?', 'wgpwpp')) /* line 39 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('WEDOS was founded in 2009, and has since grown to become the largest web hosting provider in the Czech Republic. The company invests in technology, innovation, and research in order to offer quality services for affordable prices to the widest possible customer range. WEDOS operates two privately-owned data centres, and is about to build a third one.', 'wgpwpp')) /* line 40 */;
		echo '</p>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('The first data centre, WEDOS DC 1 “Bunker”, was founded in 2010, and modernized between 2018 and 2020. The second data centre, titled WEDOS DC 2 “Podskalí”, is a unique project designed to cool servers using oil and water.', 'wgpwpp')) /* line 41 */;
		echo '</p>
                    </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('How does the activation process of the service work?', 'wgpwpp')) /* line 47 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('How does the activation process of the service work?', 'wgpwpp')) /* line 49 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('The plugin on its own, without activating the WEDOS Global service, allows you only to set the Local WordPress Cache feature which stores frequently visited web pages on the web server, thus saving the overall load on the server, and speeding the website up. If you want to take full advantage of our WEDOS Global infrastructure, and activate the Global WordPress Cache feature, click on the Activate button on the introductory page of our plugin. This action takes you to the website login.wedos.com. Register, unless you have already done so, or log in. Verify your account using a verifiction code, confirm the DNS records change, and wait for the process to finish. The protection setup follows automatically. For a detailed guide on service activation, click here:', 'wgpwpp')) /* line 50 */;
		echo ' <span class="wgpwpp-faq-link" onclick="window.open(\'https://kb.wedos.com/en/wedos-global-en/global-protection-wordpress-plugin-activation/\')">';
		echo LR\Filters::escapeHtmlText(__('Activation guide', 'wgpwpp')) /* line 50 */;
		echo '</span></p>
                    </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('How does the plugin work?', 'wgpwpp')) /* line 56 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('How does the plugin work?', 'wgpwpp')) /* line 58 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('This plugin optimises the performance, and security of your website. Its main purpose is to allow the caching, and minimisation of files which significantly speeds up website loading times, and reduces the web server load. The plugin also provides protection against a variety of cyberattacks, including DDoS attacks, and unauthorised access. It also has a cloud firewall, bot detection feature, and other tools, which help you maintain a high level of protection of your website and its visitors. Apart from that, the plugin also offers the Security Reports feature which sends you regular security reports, informing you on detected attacks and hazards, and recommending actions for improving the overall health of your website.', 'wgpwpp')) /* line 59 */;
		echo '</p>
                    </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('What exactly am I paying for?', 'wgpwpp')) /* line 65 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('What exactly am I paying for?', 'wgpwpp')) /* line 67 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('For a small fee, according to the price list on our website, you can use the features from the paid plans, so e.g. the Global WordPress Cache feature which significantly reduces the loading times of website content, and improves the overall response times of your WordPress website around the world, resulting in improved user experience (UX), better positions in search results, more orders, or leads, and returning customers. The Local WordPress Cache feature as well as the Security Reports feature are both completely free of charge.', 'wgpwpp')) /* line 68 */;
		echo '</p>
                    </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('Do I need to register?', 'wgpwpp')) /* line 74 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('Do I need to register?', 'wgpwpp')) /* line 76 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('With the exception of the Local WordPress Cache feature, a registration is necessary to be able to set the Global WordPress Cache feature, and the Security Reports feature in the WEDOS Global administration. After the registration, we will analyse your website to find out which security measures are needed for optimum protection. Based on this analysis, we deploy the most suitable security measures, such as our cloud firewall, the blocking of harmful bots, or access regulation rules. All your personal information will be protected in accordance with the GDPR regulation, that is, ensured confidentiality, integrity, and accessibility of personal information, as well as the right to access this information, edit it, or delete it. The WEDOS company holds ISO certifications concerning information security and personally identifiable information protection in public cloud services.', 'wgpwpp')) /* line 77 */;
		echo '</p>
                    </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('Do you have an official website?', 'wgpwpp')) /* line 83 */;
		echo '</span>

                <div>
                    <h3>';
		echo LR\Filters::escapeHtmlText(__('Do you have an official website?', 'wgpwpp')) /* line 86 */;
		echo '</h3>
                    <p>';
		echo LR\Filters::escapeHtmlText(__('Yes, the company website is:', 'wgpwpp')) /* line 87 */;
		echo ' <span class="wgpwpp-faq-link" onclick="window.open(';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(__('https://www.wedos.com/cs/protection/plugin-pro-wordpress', 'wgpwpp'))) /* line 87 */;
		echo ')">';
		echo LR\Filters::escapeHtmlText(__('https://www.wedos.com/cs/protection/plugin-pro-wordpress', 'wgpwpp')) /* line 87 */;
		echo '</span></p>
                </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('Can I try the service first?', 'wgpwpp')) /* line 93 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('Can I try the service first?', 'wgpwpp')) /* line 95 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('Yes, you can try the WEDOS Global Protection service first, as well as the Global WordPress Cache feature, within a free trial of the Advanced plan to be able to get to know the service and decide whether it is the right solution for you. The trial period of the Advanced plan is 180 days. We also offer a free version of the Start plan for the users of our plugin. It is designed for individuals who want to protect their website projects, or websites without using any of the paid versions of our plans.The Local WordPress Cache feature as well as the Security Reports feature are both free of charge.', 'wgpwpp')) /* line 96 */;
		echo '</p>
                    </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('Do you collect personal information?', 'wgpwpp')) /* line 102 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('Do you collect personal information?', 'wgpwpp')) /* line 104 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('Yes, we collect personal information but it is important to note that any data collection, or processing takes place in accordance with the GDPR regulation (General Data Protection Regulation). Our company cares about the protection of our customers’ privacy, and we treat personal information with the highest level of responsibility. We ensure that all your information is processed transparently, safely, and in accordance with the legal code. We always try to keep the collection of personal data at the minimum level, and we use it only for the purposes necessary for providing our services, and their improvement.', 'wgpwpp')) /* line 105 */;
		echo '</p>
                    </div>
                </a>
            </li>
            <li>
                <a>
                    <span>';
		echo LR\Filters::escapeHtmlText(__('What happens if I deactivate or uninstall the plugin?', 'wgpwpp')) /* line 111 */;
		echo '</span>
                    <div>
                        <h3>';
		echo LR\Filters::escapeHtmlText(__('What happens if I deactivate or uninstall the plugin?', 'wgpwpp')) /* line 113 */;
		echo '</h3>
                        <p>';
		echo LR\Filters::escapeHtmlText(__('After you deactivate or uninstall the plugin, your WEDOS Global account, and the service itself will stay active. This means that the plugin will no longer be visible in your WordPress administration but your domain will still be protected. The deactivation or uninstalling of the plugin has no effect on already protected domains. It is important to note that if you want to use the plugin again, you will have to install, and activate it again. For the complete cancellation of your service, and the deletion of your account, please proceed to the customer administration on the website client.wedos.global, and perform the necessary changes there.', 'wgpwpp')) /* line 114 */;
		echo '</p>
                    </div>
                </a>
            </li>
        </ul>
    </aside>
</div>

';
		$this->renderBlock('css', get_defined_vars()) /* line 122 */;
		echo '

';
		$this->renderBlock('js', get_defined_vars()) /* line 125 */;
		echo '


<aside id="process">
    <div>
        <h3>';
		echo LR\Filters::escapeHtmlText(sprintf(__("Step %d", 'wgpwpp'), $step)) /* line 168 */;
		echo ': ';
		$this->renderBlock('progressBarTitle', get_defined_vars()) /* line 168 */;
		echo '</h3>
        <ul>
            <li class="';
		if ($step >= 1) /* line 170 */ {
			echo 'done';
		}
		echo '"></li>
            <li class="';
		if ($step >= 2) /* line 171 */ {
			echo 'done';
		}
		echo '"></li>
            <li class="';
		if ($step >= 3) /* line 172 */ {
			echo 'done';
		}
		echo '"></li>
            <li class="';
		if ($step >= 4) /* line 173 */ {
			echo 'done';
		}
		echo '"></li>
            <li class="';
		if ($step >= 5) /* line 174 */ {
			echo 'done';
		}
		echo '"></li>
        </ul>
    </div>
</aside>






';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['notice' => '5'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}


	/** {block contentId} on line 2 */
	public function blockContentId(array $ʟ_args): void
	{
		echo 'home';
	}


	/** {block notices} on line 4 */
	public function blockNotices(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		echo '    <div class="wgpwpp-notices">
';
		$iterations = 0;
		foreach ($notices as $notice) /* line 5 */ {
			echo '        ';
			echo LR\Filters::escapeHtmlText($notice->render()) /* line 6 */;
			echo "\n";
			$iterations++;
		}
		echo '    </div>
';
	}


	/** {block main} on line 11 */
	public function blockMain(array $ʟ_args): void
	{
		
	}


	/** {block progress} on line 14 */
	public function blockProgress(array $ʟ_args): void
	{
		
	}


	/** {block css} on line 122 */
	public function blockCss(array $ʟ_args): void
	{
		
	}


	/** {block js} on line 125 */
	public function blockJs(array $ʟ_args): void
	{
		echo '<script>
  const wgpwpp_copy_to_clipboard = async (wrapper, target) => {
    if (!target)
      target = wrapper;

    try {
      await navigator.clipboard.writeText(target.innerHTML);
      wrapper.classList.add(\'copied\');
      setTimeout(function() { wrapper.classList.remove(\'copied\'); }, 500);
    }
    catch (err)
    {
      const textArea = document.createElement("textarea");
      textArea.value = target.innerHTML;
      textArea.style.position = "absolute";
      textArea.style.left = "-999999px";

      document.body.prepend(textArea);
      textArea.select();

      try
      {
        document.execCommand(\'copy\');
        wrapper.classList.add(\'copied\');
        setTimeout(function() { wrapper.classList.remove(\'copied\'); }, 500);
      }
      catch (err)
      {
        window.alert(';
		echo LR\Filters::escapeJs(__('Failed to copy to clipboard.', 'wgpwpp')) /* line 154 */;
		echo ');
      }
      finally
      {
        textArea.remove();
      }
    }
  };
</script>
';
	}


	/** {block progressBarTitle} on line 168 */
	public function blockProgressBarTitle(array $ʟ_args): void
	{
		
	}


	/** {define doneSvg} on line 179 */
	public function blockDoneSvg(array $ʟ_args): void
	{
		echo '<svg class="h-14" width="83" height="83" viewBox="0 0 83 83" fill="none" xmlns="http://www.w3.org/2000/svg">
<circle cx="41.5" cy="41.5" r="41.5" fill="#56CD65"></circle>
<path d="M22.2109 42.0845L34.7778 54.6514L60.7884 28.6408" stroke="white" stroke-width="8.1831" stroke-linecap="round"></path>
</svg>

';
	}


	/** {define errorSvg} on line 187 */
	public function blockErrorSvg(array $ʟ_args): void
	{
		echo '   <svg class="h-14" width="142" height="142" viewBox="0 0 142 142" fill="none" xmlns="http://www.w3.org/2000/svg">
<circle cx="71" cy="71" r="71" fill="#CD5656"></circle>
<path d="M45.5 97L97 45.5" stroke="white" stroke-width="16.2022" stroke-linecap="round"></path>
<path d="M45.5 45.5L97 97" stroke="white" stroke-width="16.2022" stroke-linecap="round"></path>
</svg>

';
	}


	/** {define loader} on line 196 */
	public function blockLoader(array $ʟ_args): void
	{
		echo ' <svg class="loader" width="135" height="140" viewBox="0 0 135 140" xmlns="http://www.w3.org/2000/svg" fill="#fff">
    <rect y="10" width="15" height="120" rx="6">
        <animate attributeName="height"
             begin="0.5s" dur="1s"
             values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
             repeatCount="indefinite"></animate>
        <animate attributeName="y"
             begin="0.5s" dur="1s"
             values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
             repeatCount="indefinite"></animate>
    </rect>
    <rect x="30" y="10" width="15" height="120" rx="6">
        <animate attributeName="height"
             begin="0.25s" dur="1s"
             values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
             repeatCount="indefinite"></animate>
        <animate attributeName="y"
             begin="0.25s" dur="1s"
             values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
             repeatCount="indefinite"></animate>
    </rect>
    <rect x="60" width="15" height="140" rx="6">
        <animate attributeName="height"
             begin="0s" dur="1s"
             values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
             repeatCount="indefinite"></animate>
        <animate attributeName="y"
             begin="0s" dur="1s"
             values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
             repeatCount="indefinite"></animate>
    </rect>
    <rect x="90" y="10" width="15" height="120" rx="6">
        <animate attributeName="height"
             begin="0.25s" dur="1s"
             values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
             repeatCount="indefinite"></animate>
        <animate attributeName="y"
             begin="0.25s" dur="1s"
             values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
             repeatCount="indefinite"></animate>
    </rect>
    <rect x="120" y="10" width="15" height="120" rx="6">
        <animate attributeName="height"
             begin="0.5s" dur="1s"
             values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
             repeatCount="indefinite"></animate>
        <animate attributeName="y"
             begin="0.5s" dur="1s"
             values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
             repeatCount="indefinite"></animate>
    </rect>
</svg>
';
	}

}
