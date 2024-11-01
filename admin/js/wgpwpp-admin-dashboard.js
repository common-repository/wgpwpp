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


    const Wgpwpp_Admin_Dashboard = {

        timeoutID: null,

        init: function()
        {
            let wp_cache_checkbox = document.getElementById('wgpwpp_wp_cache_checkbox');
            if (wp_cache_checkbox)
                wp_cache_checkbox.addEventListener('change', this.wp_cache_toggle);

            let dismiss_rating_btn = document.getElementById('wgpwpp_dismiss_rating_button');
            if (dismiss_rating_btn)
                dismiss_rating_btn.addEventListener('click', this.dismiss_rating);

            if (wgpwpp_admin_dashboard.service_active)
            {
                let cdn_cache_checkbox = document.getElementById('wgpwpp_cdn_cache_checkbox');
                if (cdn_cache_checkbox)
                    cdn_cache_checkbox.addEventListener('change', this.cdn_cache_toggle);
            }

            let dismiss_wp_cache_recommendation_button = document.getElementById('wgpwpp_cache_recommendation_dismiss');
            if (dismiss_wp_cache_recommendation_button)
                dismiss_wp_cache_recommendation_button.addEventListener('click', function() { Wgpwpp_Admin_Dashboard.dissmiss_wp_cache_recommendation(); });
        },

        dissmiss_wp_cache_recommendation: function()
        {
            let recommendation = document.getElementById('wgpwpp_wp_cache_recommendation');
            if (!recommendation)
                return;

            recommendation.style.display = 'none';
        },

        show_wp_cache_recommendation: function()
        {
            let recommendation = document.getElementById('wgpwpp_wp_cache_recommendation');
            if (!recommendation)
                return;

            recommendation.style.display = 'block';
        },

        dismiss_rating: function(event)
        {
            event.preventDefault();
            document.getElementById("wgpwpp_rating_section").style.display = "none";

            let data = new FormData();
            data.append('action', 'wgpwpp_dashboard_dismiss_rating');
            data.append('_ajax_nonce', wgpwpp_admin_dashboard.nonce_dismiss_rating);

            fetch(wgpwpp_admin_dashboard.ajaxurl, {
                method: 'POST',
                body: data
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.result === 'error') {
                    event.target.checked = !event.target.checked;
                    console.error(data.msg);
                }
                else
                {
                    console.log(data);
                }
            })
        },

        cdn_cache_toggle: function(event)
        {
            let data = new FormData();
            data.append('action', 'wgpwpp_dashboard_toggle_cdn_cache');
            data.append('_ajax_nonce', wgpwpp_admin_dashboard.nonce_toggle_cdn_cache);
            data.append('status', event.target.checked);

            fetch(wgpwpp_admin_dashboard.ajaxurl, {
                method: 'POST',
                body: data
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.result === 'error')
                    event.target.checked = !event.target.checked;

                Wgpwpp_Admin_Dashboard.draw_notice(data.notice, 'wgpwpp_cdn_cache');
            })
        },

        wp_cache_toggle: function(event)
        {
            let data = new FormData();
            data.append('action', 'wgpwpp_dashboard_toggle_wp_cache');
            data.append('_ajax_nonce', wgpwpp_admin_dashboard.nonce_toggle_wp_cache);
            data.append('status', event.target.checked);

            fetch(wgpwpp_admin_dashboard.ajaxurl, {
                method: 'POST',
                body: data
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.result === 'error')
                    event.target.checked = !event.target.checked;

                if (data.status)
                    Wgpwpp_Admin_Dashboard.dissmiss_wp_cache_recommendation();
                else
                    Wgpwpp_Admin_Dashboard.show_wp_cache_recommendation();

                Wgpwpp_Admin_Dashboard.draw_notice(data.notice, 'wgpwpp_wp_cache');
            })
        },

        draw_notice: function(notice,id)
        {
          let notices_wrapper = document.getElementById('wgpwpp_dashboard_notices');

          let notice_wrapper = document.getElementById(id);
          if (!notice_wrapper)
          {
              notice_wrapper = document.createElement('div');
              notice_wrapper.id = id;
          }
          else if (typeof this.timeoutID !== null)
          {
              clearTimeout(this.timeoutID);
          }

          notice_wrapper.innerHTML = notice;

          this.timeoutID = setTimeout(function() { notice_wrapper.parentElement.removeChild(notice_wrapper); }, 5000);

          notices_wrapper.appendChild(notice_wrapper);
        },

        load_chart_data: function(type)
        {
            let data = new FormData();
            data.append('action', 'wgpwpp_dashboard_load_opensearch_data');
            data.append('_ajax_nonce', wgpwpp_admin_dashboard.nonce_opensearch_data);
            data.append('type', type);

            fetch(wgpwpp_admin_dashboard.ajaxurl, {
                method: 'POST',
                body: data
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.result === 'error') {
                    console.log('ERROR AJAX')
                }
                else
                {
                    this.draw_chart(data);
                }
            })
        },

        draw_chart: function(data) {
            let sum = 0;
            let max = 0;

            for (let key in data.values)
            {
                if (!data.values.hasOwnProperty(key))
                    continue;

                let value = data.values[key];

                sum += value;

                if (value > max)
                    max = value;
            }

            let sum_wrapper = document.getElementById('wgpwpp_total_'+data.type);
            sum_wrapper.innerText = sum.toString();

            let rate = 150/max;

            let spinner = document.getElementById('wgpwpp_chart_'+data.type+'_spinner');
            spinner.style.display = 'none';

            let i = 1;
            for (let date in data.values)
            {
                if (!data.values.hasOwnProperty(date))
                    continue;

                let cnt = data.values[date];

                let height = Math.round(rate * cnt);
                if (cnt === 0)
                    height = 1;

                let color = '';
                if (data.type === 'ddos')
                    color = (height < 75) ? '#f9d2d7' : '#ec7482';
                else
                    color = (height < 75) ? '#9dc3fb' : '#3b86f7';

                let bar = document.getElementById('wgpwpp_chart_bar_'+data.type+'_'+(i-1));
                bar.style.height = height+'px';
                bar.style.backgroundColor = color;

                let cnt_wrapper = document.getElementById('wgpwpp_chart_bar_tooltip_'+data.type+'_cnt_'+(i-1));
                cnt_wrapper.innerText = cnt.toString();

                let date_wrapper = document.getElementById('wgpwpp_chart_bar_tooltip_'+data.type+'_date_'+(i-1));
                date_wrapper.innerText = date;

                i++;
            }

        }
    }

    $(function() {
        Wgpwpp_Admin_Dashboard.init();

        if (wgpwpp_admin_dashboard.service_active) {
            Wgpwpp_Admin_Dashboard.load_chart_data('ddos');
            Wgpwpp_Admin_Dashboard.load_chart_data('cache');
        }
    });



})( jQuery );


