;(function($) {
    tinymce.create('tinymce.plugins.dt_tooltip', {
       init : function(el, url) {
    	   el.addButton('dt_tooltip_button', {
                title : dtAdminL10n.i18n_tooltip_mce_button,
                image : dtAdminL10n.framework_assets_url + '/images/tooltip.png',
                onclick: function() {
                    var width = $(window).width(), H = $(window).height(), W = (500 < width) ? 500 : width;
                    W = 500;
                    H = 300;
                    tb_show(dtAdminL10n.i18n_tooltip_mce_button, '#TB_inline?width=' + W + '&height=' + H + '&inlineId=dt_tooltip_form');
                    var TB_overlay = $('#TB_overlay');
                    TB_overlay.css({
                    	'z-index':999999
                    });
                    var TB_window = $('#TB_window');
                    TB_window.css({
                    	'z-index':999999
                    });
                    var TB_ajaxContent = $('#TB_ajaxContent');
                    TB_ajaxContent.css({
                    	'height':'100%',
                    	'width':'100%',
                    	'padding':'0',
                    	'margin':'0 auto'
                    });
                }	
            });
        },
    });
    tinymce.PluginManager.add('dt_tooltip', tinymce.plugins.dt_tooltip);
})(jQuery);