(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */


    const Wgpwpp_Admin_Cache = {
        init: function() {
            setTimeout(function() { document.getElementById('wgpwpp-cache-notices').innerText = ''; }, 5000);

            let cache_button = document.getElementById('wgpwpp-cache-toggle-button');

            let button_action = function(event) {
                let wrapper = document.getElementById("wgpwpp-button-spinner");
                let spinner = document.createElement('span');
                spinner.classList.add('spinner');
                spinner.classList.add('is-active');
                wrapper.innerText = "";
                wrapper.append(spinner);

                event.target.form.submit();
                event.target.disabled = true;
            };

            cache_button.addEventListener('change', button_action);

            let purge_cache_button = document.getElementById('wgpwpp-purge-cache');
            if (purge_cache_button)
              purge_cache_button.addEventListener('click', this.purge_cache);
        },

        purge_cache: function(event) {
            event.target.disabled = true;

            let data = new FormData();
            data.append('action', event.target.dataset.action);
            data.append('_ajax_nonce', wgpwpp_cache_settings.nonce_purge_cache);

            let wrapper = document.getElementById("wgpwpp-button-spinner");
            let spinner = document.createElement('span');
            spinner.classList.add('spinner');
            spinner.classList.add('is-active');
            wrapper.innerText = "";
            wrapper.append(spinner);

            fetch(wgpwpp_cache_settings.ajax_url, {
                method: 'POST',
                body: data
            })
            .then(() => { window.location.reload(); })
        },


  }

  $(function() {
      Wgpwpp_Admin_Cache.init();
  });

})( jQuery );

