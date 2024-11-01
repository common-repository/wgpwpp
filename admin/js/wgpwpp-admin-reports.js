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


    const Wgpwpp_Admin_Reports = {

        init: function()
        {
            let buttons = document.querySelectorAll('.wgpwpp-btn-checkbox');
            let flags = document.querySelectorAll('.wgpwpp-flag-checkbox');

            let button_action = function(event) {
                let wrapper = document.getElementById(event.target.name + "-spinner");
                let spinner = document.createElement('span');
                spinner.classList.add('spinner');
                spinner.classList.add('is-active');
                wrapper.innerText = "";
                wrapper.append(spinner);

                event.target.form.submit();
                event.target.disabled = true;
            };

            let flag_action = function(event) {
                let wrapper = document.getElementById("wgpwpp-reports-flags-spinner");
                let spinner = document.createElement('span');
                spinner.classList.add('spinner');
                spinner.classList.add('is-active');
                wrapper.innerText = "";
                wrapper.append(spinner);

                event.target.form.submit();
                event.target.disabled = true;
            };

            buttons.forEach((button) => {
                button.addEventListener('change', button_action)
            });

            flags.forEach((flag) => {
                flag.addEventListener('change', flag_action)
            });
        },

    }

    $(function() {
        Wgpwpp_Admin_Reports.init();
    });

})( jQuery );


