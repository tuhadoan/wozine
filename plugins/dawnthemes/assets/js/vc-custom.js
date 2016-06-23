function pricing_table_feature_remove(element){
	var $this = jQuery(element);
	$this.closest('tr').remove();
	return false;
}
function pricing_table_feature_add(element){
	var $this = jQuery(element);
	var option_list = $this.closest('.pricing-table-feature-list'),
		option_table = option_list.find('table tbody');
	var tmpl = jQuery(dtvcL10n.pricing_table_feature_tmpl);
	option_table.append(tmpl);
	return false;
}
;(function ($) {
	"use strict";
	if(_.isUndefined(window.vc))
		return;
	vc.edit_form_callbacks.push(function() {
		var model = this.$el;
		var pricing_table_feature = model.find('.pricing-table-feature-list');
		if(pricing_table_feature.length){
			var features = [];
			pricing_table_feature.find('table tbody tr').each(function(){
				var $this = $(this);
				var feature = {};
				feature['content'] = $this.find('#content').val();
				features.push(feature);
			});
			if(_.isEmpty(features)){
				this.params.features='';
			}else{
				var features_json = JSON.stringify(features);
				this.params.features = base64_encode(features_json);
			}
		}
	});
	
	var Shortcodes = vc.shortcodes;
	window.DHVCCarousel = window.VcTabsView.extend({
		render:function () {
            window.DHVCCarousel.__super__.render.call(this);
            return this;
        },
        ready:function (e) {
            window.DHVCCarousel.__super__.ready.call(this, e);
            return this;
        },
        createAddTabButton:function () {
            var new_tab_button_id = (+new Date() + '-' + Math.floor(Math.random() * 11));
            this.$tabs.append('<div id="new-tab-' + new_tab_button_id + '" class="new_element_button"></div>');
            this.$add_button = $('<li class="add_tab_block"><a href="#new-tab-' + new_tab_button_id + '" class="add_tab" title="' + window.dtvcL10n.add_item_title + '"></a></li>').appendTo(this.$tabs.find(".tabs_controls"));
        },
        addTab:function (e) {
            e.preventDefault();
            this.new_tab_adding = true;
            var tab_title = window.dtvcL10n.item_title,
                tabs_count = this.$tabs.find('[data-element_type=dt_carousel_item]').length,
                tab_id = (+new Date() + '-' + tabs_count + '-' + Math.floor(Math.random() * 11));
            vc.shortcodes.create({shortcode:'dt_carousel_item', params:{title:tab_title + ' ' + (tabs_count + 1), tab_id:tab_id}, parent_id:this.model.id});
            return false;
        },
        cloneModel:function (model, parent_id, save_order) {
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                model_clone,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_carousel_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_carousel_item]').length + '-' + Math.floor(Math.random() * 11)});
            model_clone = Shortcodes.create({shortcode:model.get('shortcode'), id:vc_guid(), parent_id:parent_id, order:new_order, cloned:(model.get('shortcode') === 'dt_carousel_item' ? false : true), cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.get('id'), true);
            }, this);
            return model_clone;
        }
	});
	window.DHVCCarouselItem = window.VcTabView.extend({
		render:function () {
            window.DHVCCarouselItem.__super__.render.call(this);
            return this;
        },
        ready:function (e) {
            window.DHVCCarouselItem.__super__.ready.call(this, e);
            return this;
        },
        cloneModel:function (model, parent_id, save_order) {
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_carousel_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_carousel_item]').length + '-' + Math.floor(Math.random() * 11)});
            var model_clone = Shortcodes.create({shortcode:model.get('shortcode'), parent_id:parent_id, order:new_order, cloned:true, cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.id, true);
            }, this);
            return model_clone;
        }
	});
	window.DHVCTestimonial = window.VcTabsView.extend({
		render:function () {
            window.DHVCTestimonial.__super__.render.call(this);
            return this;
        },
        ready:function (e) {
            window.DHVCTestimonial.__super__.ready.call(this, e);
            return this;
        },
        createAddTabButton:function () {
            var new_tab_button_id = (+new Date() + '-' + Math.floor(Math.random() * 11));
            this.$tabs.append('<div id="new-tab-' + new_tab_button_id + '" class="new_element_button"></div>');
            this.$add_button = $('<li class="add_tab_block"><a href="#new-tab-' + new_tab_button_id + '" class="add_tab" title="' + window.dtvcL10n.add_item_title + '"></a></li>').appendTo(this.$tabs.find(".tabs_controls"));
        },
        addTab:function (e) {
            e.preventDefault();
            this.new_tab_adding = true;
            var tab_title = window.dtvcL10n.item_title,
                tabs_count = this.$tabs.find('[data-element_type=dt_testimonial_item]').length,
                tab_id = (+new Date() + '-' + tabs_count + '-' + Math.floor(Math.random() * 11));
            vc.shortcodes.create({shortcode:'dt_testimonial_item', params:{title:tab_title + ' ' + (tabs_count + 1), tab_id:tab_id}, parent_id:this.model.id});
            return false;
        },
        cloneModel:function (model, parent_id, save_order) {
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                model_clone,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_testimonial_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_testimonial_item]').length + '-' + Math.floor(Math.random() * 11)});
            model_clone = Shortcodes.create({shortcode:model.get('shortcode'), id:vc_guid(), parent_id:parent_id, order:new_order, cloned:(model.get('shortcode') === 'dt_testimonial_item' ? false : true), cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.get('id'), true);
            }, this);
            return model_clone;
        }
	});
	window.DHVCTestimonialItem = window.VcTabView.extend({
		render:function () {
            window.DHVCTestimonialItem.__super__.render.call(this);
            return this;
        },
        ready:function (e) {
            window.DHVCTestimonialItem.__super__.ready.call(this, e);
            return this;
        },
        cloneModel:function (model, parent_id, save_order) {
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_testimonial_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_testimonial_item]').length + '-' + Math.floor(Math.random() * 11)});
            var model_clone = Shortcodes.create({shortcode:model.get('shortcode'), parent_id:parent_id, order:new_order, cloned:true, cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.id, true);
            }, this);
            return model_clone;
        }
	});
	window.DHVCPricingTable = window.VcTabsView.extend({
		render:function () {
            window.DHVCPricingTable.__super__.render.call(this);
            return this;
        },
        ready:function (e) {
            window.DHVCPricingTable.__super__.ready.call(this, e);
            return this;
        },
        createAddTabButton:function () {
            var new_tab_button_id = (+new Date() + '-' + Math.floor(Math.random() * 11));
            this.$tabs.append('<div id="new-tab-' + new_tab_button_id + '" class="new_element_button"></div>');
            this.$add_button = $('<li class="add_tab_block"><a href="#new-tab-' + new_tab_button_id + '" class="add_tab" title="' + window.dtvcL10n.add_item_title + '"></a></li>').appendTo(this.$tabs.find(".tabs_controls"));
        },
        addTab:function (e) {
            e.preventDefault();
            this.new_tab_adding = true;
            var tab_title = window.dtvcL10n.item_title,
                tabs_count = this.$tabs.find('[data-element_type=dt_pricing_table_item]').length,
                tab_id = (+new Date() + '-' + tabs_count + '-' + Math.floor(Math.random() * 11));
            if(tabs_count  >= 5 ){
            	alert(window.dtvcL10n.pricing_table_max_item_msg);
            	return false;
            }
            vc.shortcodes.create({shortcode:'dt_pricing_table_item', params:{title:tab_title + ' ' + (tabs_count + 1), tab_id:tab_id}, parent_id:this.model.id});
            return false;
        },
        cloneModel:function (model, parent_id, save_order) {
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                model_clone,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_pricing_table_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_pricing_table_item]').length + '-' + Math.floor(Math.random() * 11)});
            model_clone = Shortcodes.create({shortcode:model.get('shortcode'), id:vc_guid(), parent_id:parent_id, order:new_order, cloned:(model.get('shortcode') === 'dt_pricing_table_item' ? false : true), cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.get('id'), true);
            }, this);
            return model_clone;
        }
	});
	window.DHVCPricingTableItem = window.VcTabView.extend({
		render:function () {
            window.DHVCPricingTableItem.__super__.render.call(this);
            return this;
        },
        ready:function (e) {
            window.DHVCPricingTableItem.__super__.ready.call(this, e);
            return this;
        },
        cloneModel:function (model, parent_id, save_order) {
        	if(this.$tabs.find('[data-element_type=dt_pricing_table_item]').length >= 5){
        		alert(window.dtvcL10n.pricing_table_max_item_msg);
            	return false;
        	}
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_pricing_table_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_pricing_table_item]').length + '-' + Math.floor(Math.random() * 11)});
            var model_clone = Shortcodes.create({shortcode:model.get('shortcode'), parent_id:parent_id, order:new_order, cloned:true, cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.id, true);
            }, this);
            return model_clone;
        }
	});
	window.DHVCTimeline = window.VcTabsView.extend({
		render:function () {
            window.DHVCTimeline.__super__.render.call(this);
            var params = this.model.get('params');
            this.$el.data('timeline-type',params.type);
            return this;
        },
        ready:function (e) {
            window.DHVCTimeline.__super__.ready.call(this, e);
            return this;
        },
        changeShortcodeParams:function (model) {
        	window.DHVCTimeline.__super__.changeShortcodeParams.call(this, model);
        	var params = model.get('params');
        	this.$el.data('timeline-type',params.type);
        	var timeline = this.$el;
        	this.$tabs.find('[data-element_type=dt_timeline_item]').each(function(index,tab){
        		var tab_params = $(tab).data('model').get('params');
        		tab_params.badge_type = timeline.data('timeline-type');
        		$(tab).data('model').save({params:tab_params});
        	}) ;
        },
        createAddTabButton:function () {
            var new_tab_button_id = (+new Date() + '-' + Math.floor(Math.random() * 11));
            this.$tabs.append('<div id="new-tab-' + new_tab_button_id + '" class="new_element_button"></div>');
            this.$add_button = $('<li class="add_tab_block"><a href="#new-tab-' + new_tab_button_id + '" class="add_tab" title="' + window.dtvcL10n.add_item_title + '"></a></li>').appendTo(this.$tabs.find(".tabs_controls"));
        },
        addTab:function (e) {
            e.preventDefault();
            this.new_tab_adding = true;
            var tab_title = window.dtvcL10n.item_title,
                tabs_count = this.$tabs.find('[data-element_type=dt_timeline_item]').length,
                badge_type = this.$el.data('timeline-type'),
                tab_id = (+new Date() + '-' + tabs_count + '-' + Math.floor(Math.random() * 11));
            vc.shortcodes.create({shortcode:'dt_timeline_item', params:{title:tab_title + ' ' + (tabs_count + 1), tab_id:tab_id, badge_type:badge_type}, parent_id:this.model.id});
            return false;
        },
        cloneModel:function (model, parent_id, save_order) {
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                model_clone,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_timeline_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_timeline_item]').length + '-' + Math.floor(Math.random() * 11)});
            model_clone = Shortcodes.create({shortcode:model.get('shortcode'), id:vc_guid(), parent_id:parent_id, order:new_order, cloned:(model.get('shortcode') === 'dt_timeline_item' ? false : true), cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.get('id'), true);
            }, this);
            return model_clone;
        }
	});
	window.DHVCTimelineItem = window.VcTabView.extend({
		render:function () {
            window.DHVCTimelineItem.__super__.render.call(this);
            return this;
        },
        ready:function (e) {
            window.DHVCTimelineItem.__super__.ready.call(this, e);
            return this;
        },
        changeShortcodeParams:function (model) {
        	window.DHVCTimelineItem.__super__.changeShortcodeParams.call(this, model);
        	var params = model.get('params');
        	var timeline = this.$el.closest('[data-element_type="dt_timeline"]');
        	params.badge_type = timeline.data('timeline-type');
        	model.save({params:params});
        },
        cloneModel:function (model, parent_id, save_order) {
            var shortcodes_to_resort = [],
                new_order = _.isBoolean(save_order) && save_order === true ? model.get('order') : parseFloat(model.get('order')) + vc.clone_index,
                new_params = _.extend({}, model.get('params'));
            if (model.get('shortcode') === 'dt_timeline_item') _.extend(new_params, {tab_id:+new Date() + '-' + this.$tabs.find('[data-element_type=dt_timeline_item]').length + '-' + Math.floor(Math.random() * 11)});
            var model_clone = Shortcodes.create({shortcode:model.get('shortcode'), parent_id:parent_id, order:new_order, cloned:true, cloned_from:model.toJSON(), params:new_params});
            _.each(Shortcodes.where({parent_id:model.id}), function (shortcode) {
                this.cloneModel(shortcode, model_clone.id, true);
            }, this);
            return model_clone;
        }
	});
})(window.jQuery);
