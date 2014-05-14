/* dashEE jQuery Plugin JS File for interactive widgets */
(function($){

    $.fn.dasheeGetProxy = function(options) {
        var $widget = this.parents('li');
        var $params = {
            'config_id':$('input[name="config_id"]').val(),
            'wgtid': $widget.attr('id'),
            'mthd': options.method,
            };

        $.ajax({
            type: 'GET',
            url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_widget_get_proxy',
            data: $.extend($params, options.params),
            success: function(html) {
                if($.isFunction(options.success)) {
                    options.success.call(this, html);
                }
            },
            error: function(html) {
                if($.isFunction(options.error)) {
                    options.error.call(this, html);
                }
                else {
                    $.ee_notice("Nope, there was a problem.", {type: 'error', open: true});
                }
            }
        });
    };

    $.fn.dasheePostProxy = function(options) {
        var $widget = this.parents('li');
        var $params = {
            'config_id':$('input[name="config_id"]').val(),
            'wgtid': $widget.attr('id'),
            'mthd': options.method,
            };

        $.ajax({
            type: 'POST',
            url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_widget_post_proxy',
            data: $.extend($params, options.params),
            success: function(html) {
                if($.isFunction(options.success)) {
                    options.success.call(this, html);
                }
            },
            error: function(html) {
                if($.isFunction(options.error)) {
                    options.error.call(this, html);
                }
                else {
                    $.ee_notice("Nope, there was a problem.", {type: 'error', open: true});
                }
            }
        });
    };

})(jQuery);