;(function($) {
	$(document).ready(function(){
		//Tooltip Shortcode popup
		$('<div id="dt_tooltip_form">'+dtAdminL10n.tooltip_form+'</div>').appendTo('body').hide();
		var dt_tooltip_form = $('.dt-tooltip-form');
		dt_tooltip_form.find('#type').on('change',function(){
			dt_tooltip_form.find('input#title').closest('div').hide();
			console.log(dt_tooltip_form);
			if($(this).val() == 'popover'){
				dt_tooltip_form.find('input#title').closest('div').show();
			}
		});
		dt_tooltip_form.find("#dt-tooltip-cancel > a").click(function(e){
			e.stopPropagation();
			e.preventDefault();
			tb_remove();
		});
		dt_tooltip_form.find('#dt-tooltip-update > input[type="button"]').click(function(e){
			e.stopPropagation();
			e.preventDefault();
			var shortcode = '[dt_tooltip ';
			$('.dt-tooltip-form input,.dt-tooltip-form select').each(function(){
				if($(this).is('input[type="text"]') && $(this).val() != ''){
	   				 shortcode += ' '+$(this).data('id')+ '="'+$(this).val()+'"';
	   			 }else if($(this).is('select')){
	   				 shortcode += ' '+ $(this).data('id') + '="'+$(this).find('option:selected').attr('value')+'"';
	   			 }
			});
			shortcode +=']';
			if(dt_tooltip_form.find("#content").val() != ''){
				shortcode += dt_tooltip_form.find('[data-id="content"]').val();
			}
			shortcode +='[/dt_tooltip]';
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
			tb_remove();
		});
		
		var update_megamenu_fields = function(){			
			var menu_li_items = $( '.menu-item');
			menu_li_items.each( function( i ) 	{
				var megamenu_status = $( '.edit-menu-item-megamenu-status', this );
				if( ! $( this ).is( '.menu-item-depth-0' ) ) {
					var check_against = menu_li_items.filter( ':eq(' + (i-1) + ')' );

					if( check_against.is( '.enable-megamenu' ) ) {
						megamenu_status.attr( 'checked', 'checked' );
						$( this ).addClass( 'enable-megamenu' );
					} else {
						megamenu_status.attr( 'checked', '' );
						$( this ).removeClass( 'enable-megamenu' );
					}
				} else {
					if( megamenu_status.attr( 'checked' ) ) {
						$( this ).addClass( 'enable-megamenu' );
					}
				}
			});
		};
		
		update_megamenu_fields();
		
		//Mega menu
		$( document ).on( 'click', '.edit-menu-item-megamenu-status', function() {
			var parent_li_item = $( this ).parents( '.menu-item:eq( 0 )' );

			if( $( this ).is( ':checked' ) ) {
				parent_li_item.addClass( 'enable-megamenu' );
			} else 	{
				parent_li_item.removeClass( 'enable-megamenu' );
			}
			update_megamenu_fields();
		});
		
		$( '.dt-colorpicker' ).wpColorPicker();
	});
})(jQuery);