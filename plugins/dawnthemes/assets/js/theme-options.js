;(function($){
	'use strict';
	var DTOptions = {
		init: function(){
			var self = this;
			$('.dt-image-select img').tooltip({
				position: {
					my: "center bottom-5",
					at: "center top"
				}
			});
			$(document).on('click','#dt-opt-submit2, #dt-opt-submit',function(e){
				$('#custom-css').val(self.css_editor.getValue());
				//$('#custom-js').val(self.js_editor.getValue());
				return true;
			});
			
			$(document).on('click','#dt-opt-reset',function(e){
				if(window.confirm(dtthemeoptionsL10n.reset_msg)){
					return true;
				}
				return false;
			});
			
			if(window.ace && window.ace.edit){
				this.css_editor = ace.edit('custom-css-editor');
				this.css_editor.getSession().setMode("ace/mode/css");
                this.css_editor.setTheme("ace/theme/chrome");
                this.css_editor.clearSelection();

//				this.js_editor = ace.edit('custom-js-editor');
//				this.js_editor.getSession().setMode("ace/mode/javascript");
//                this.js_editor.setTheme("ace/theme/chrome");
//                this.js_editor.clearSelection();
                
			}
			
			this.navTab();
			this.fieldInit();
			this.dependencyInit();
		},
		navTab: function(){
			var tab = $('#dt-opt-tab'),
				tabControl = tab.find('#dt-opt-menu a'),
				tabContent = tab.find('#dt-opt-content'),
				$supports_html5_storage;
			try {
				$supports_html5_storage = ( 'sessionStorage' in window && window.sessionStorage !== null );
			} catch( err ) {
				$supports_html5_storage = false;
			}
			
			if($supports_html5_storage){
				if (localStorage.getItem('dt-opt-tab')) {
					var hasTab = function(href){
			            return $('#dt-opt-menu a[href*="' + href + '"]').length;
			        };
			        if(!hasTab(localStorage.getItem('dt-opt-tab'))){
			        	localStorage.removeItem('dt-opt-tab');
		                return true;
			        }
			        $('#dt-opt-menu').find('li.current').removeClass('current');
			        var tabhref = localStorage.getItem('dt-opt-tab');
			        var $el = $('#dt-opt-menu a[href*="' + tabhref + '"]');
			        $el.closest('li').addClass('current');
			        tabContent.find('.dt-opt-section').hide();
			        tabContent.find(tabhref).show();
				}
			}
			
			tabControl.on('click',function(e){
				e.stopPropagation();
				e.preventDefault();
				
				var $this = $(this),
					target = $this.attr('href');
				
				if($this.closest('li').hasClass('current')){
					return;
				}
				
				target = target && target.replace(/.*(?=#[^\s]*$)/, '');
				
				if (!target) {
					return;
				}
				$this.closest('#dt-opt-menu').find('li.current').removeClass('current');
				$this.closest('li').addClass('current')
				tabContent.find('.dt-opt-section').hide();
				tabContent.find(target).show();
				if($supports_html5_storage){
					window.localStorage.setItem('dt-opt-tab', target);
				}
			});
		},
		dependencyHook:function(e){
			var $this = $(e.currentTarget),
				content = $this.closest('.dt-opt-content'),
				master_container = $this.closest('tr'),
				master_value,
				is_empty;
			
			master_value = $this.is(':checkbox') ? $.map($this.closest('tr').find('dt-opt-value:checked'),
	                function (element) {
						return $(element).val();
	            	})
	            : ($this.is(':radio') ? $this.closest('tr').find('.dt-opt-value:checked').val() : $this.val() );
    	  
	        is_empty = $this.is(':checkbox') ? !$this.closest('tr').find('dt-opt-value:checked').length
                 : ( $this.is(':radio') ? !$this.closest('tr').find('.dt-opt-value:checked').val() : !master_value.length )  ;
    	  
	        if(master_container.hasClass('dt-opt-hidden')){
	        	$.each( $('[data-master=' + $this.data('name') + ']'),function(){
	        		$(this).closest('tr').addClass('dt-opt-hidden');
	        	});   
		    }else{
		    	$.each( $('[data-master=' + $this.data('name') + ']'),function(){
		    	  var dependency_value = $(this).data('master-value').toString();
		    	  dependency_value = dependency_value.split(','); 
		    	  if (_.intersection((_.isArray(dependency_value) ? dependency_value : [dependency_value]), (_.isArray(master_value) ? master_value : [master_value.toString()])).length) {
		    		  $(this).closest('tr').removeClass('dt-opt-hidden');
		           } else {
		             $(this).closest('tr').addClass('dt-opt-hidden');
		           }	
		    	   $(this).find('.dt-opt-value[data-name]').trigger('change');
		    	});
		    }
	           	
		},
		dependencyInit: function(){
			var self = this;
			$.each($('.dt-dependency-field'),function(){
				var masters = $('[data-name=' + $(this).data('master') + ']');
				$(masters).bind('keyup change',self.dependencyHook);
				$.each($(masters),function(){
					self.dependencyHook({currentTarget: $(this) });
				});
			});
		},
		fieldInit: function(){
			
			$("select.dt-chosen-select").chosen();
			
			jQuery("select.dt-chosen-select-nostd").chosen({
				allow_single_deselect: 'true'
			});
			
			$('.dt-image-select li img').click(function(e){
				e.stopPropagation();
				e.preventDefault();
				$(this).closest('.dt-image-select').find('li.selected').removeClass("selected");
				$(this).closest('li').addClass('selected');
				$(this).closest('label').find('input[type="radio"]').prop('checked',false);
				$(this).closest('label').find("input[type='radio']").prop("checked", true);
			});
			$('.dt-field-buttonset').each(function() {
		        $(this).find('.dt-buttonset').buttonset();
		    }); 
			
			$('.dt-field-switch').each(function(){
				var $this = $(this);
				$this.find(".cb-enable").click(function() {
					if ($(this).hasClass('selected')) {
						return;
					}
					var parent = $(this).parents('.dt-field-switch');
					$('.cb-disable', parent).removeClass('selected');
					$(this).addClass('selected');
					$('.switch-input', parent).val('1').trigger('change');
				});
				$this.find(".cb-disable").click(function() {
					if ($(this).hasClass('selected')) {
						return;
					}
					var parent = $(this).parents('.dt-field-switch');
					$('.cb-enable', parent).removeClass('selected');
					$(this).addClass('selected');
					$('.switch-input', parent).val('0').trigger('change');
				});
				$this.find('.cb-enable span, .cb-disable span').find().attr('unselectable', 'on');
			});
		}
	}
	$(document).ready(function(){
		DTOptions.init();
	});
})(jQuery);