/*
 * Script from NETTUTS.com [by James Padolsey] modified by Chris Monnat
 * @requires jQuery($), jQuery UI & sortable/draggable UI modules
 */

(function($) {

var url = window.location.href;
url = url.split('?')[0];

var dashEE = {
	
	settings : {
		columnSelector : '.column',
		widgetSelector: '.widget',
		headingSelector: '.heading',
		buttonsSelector: '.buttons',
		contentSelector: '.widget-content',
		widgetDefault : {
			movable: true,
			removable: true,
			collapsible: true,
			editable: false
		},
		widgetIndividual : {
			dynamic : {
				movable: false,
				removable: true,
				collapsible: true,
				editable: true
			}
		}
	},

	/**
	 * Get Widgets
	 */
	getWidgets : function () {
		return $(this.settings.widgetSelector);
	},

	/**
	 * Initialize
	 */
	init : function () {
		var $widgets = this.getWidgets();

		for (var i = 0; i < $widgets.length; i++) {
			var $widget = $($widgets[i]);
			this.initWidget($widget);
		}

		this.makeSortable();
	},
	
	/**
	 * Get Widget Settings
	 */
	getWidgetSettings : function (id) {
		var widgetSettings = (id && this.settings.widgetIndividual[id]) ? this.settings.widgetIndividual[id] : {};

		return $.extend({}, this.settings.widgetDefault, widgetSettings);
	},

	/*
	 * Initialize Widget
	 */
	initWidget : function (widget) {

		var $heading = $(this.settings.headingSelector, widget),
			$buttons = $(this.settings.buttonsSelector, $heading),
			$content = $(this.settings.contentSelector, widget),
			id = widget.attr('id'),
			widgetSettings = dashEE.getWidgetSettings(widget.attr('dashee')),
			col = widget.parents(this.settings).attr('id').substr(-1);

		// -------------------------------------------
		//  Remove button
		// -------------------------------------------

		if (widgetSettings.removable) {
			var $removeBtn = $('<a href="#" title="Remove" class="remove"></a>').appendTo($buttons);

			$removeBtn.mousedown($.proxy(function (e) {
				e.stopPropagation();	
			}, this));

			$removeBtn.click($.proxy(function (e) {
				$('#dashConfirm').dialog({
					resizable: false,
					height:140,
					modal: true,
					buttons: {
						'No': function() {
							$(this).dialog("close");
						},
						'Yes': $.proxy(function() {
							$.ajax({
								type: 'GET',
								url: url + '/?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=remove_widget&col='+col+'&wgt='+id,
								dataTyle: 'html',
								success: $.proxy(function(html) {
									widget.animate({
										opacity: 0	  
									},$.proxy(function () {
										widget.wrap('<div/>').parent().slideUp($.proxy(function () {
											widget.remove();
										}, this));
									}, this));
									$.ee_notice("Widget has been removed.", {type: 'success'});
									setTimeout("$('#dashConfirm').dialog('close');", 750);
								}, this),
								error: $.proxy(function(html) {
									$.ee_notice("ERROR: The widget you selected could not be removed.", {type: 'error', open: true});
								}, this)
							});
						}, this)
					},
					title: 'Remove Widget'
				});
				return false;
			}, this));
		}

		// -------------------------------------------
		//  Settings button
		// -------------------------------------------

		if (widgetSettings.editable) {
			var $editButton = $('<a href="#" title="Settings" class="edit"></a>').appendTo($buttons);

			$editButton.mousedown($.proxy(function (e) {
				e.stopPropagation();
			}, this));

			$editButton.click($.proxy(function () {
				$content.html('<p><center><img src="'+$('#dashLoader').attr('src')+'" /></center></p>');

				$.ajax({
					type: 'GET',
					url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=widget_settings&col='+col+'&wgt='+id,
					dataTyle: 'html',
					success: $.proxy(function(html) {
						$content.html(html);
						$content.addClass('settings');

						$('form.dashForm').submit(function(event) {
							event.preventDefault();
							$content.removeClass('settings');
							$content.html('<p><center><img src="'+$('#dashLoader').attr('src')+'" /></center></p>');
		
							$.ajax({
								type: 'POST',
								url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=update_settings',
								data: $(this).serialize()+'&col='+col+'&wgt='+id,
								dataTyle: 'json',
								success: function(html) {
									var response = $.parseJSON(html);
									$('h2', $heading).html(response.title);
									$content.html(response.content);
								},
								error: function(html) {
									$content.html('<p>There was a problem.</p>');
								}
							});
		
						});
					}, this),
					error: $.proxy(function(html) {
						$content.html('<p>There was a problem.</p>');
					}, this)
				});
			}, this));
		}

		// -------------------------------------------
		//  Collapse button
		// -------------------------------------------

		if (widgetSettings.collapsible) {
			var $collapseButton = $('<a href="#" title="Collapse" class="collapse"></a>').appendTo($buttons);

			$collapseButton.mousedown($.proxy(function (e) {
				widget.toggleClass('collapsed')
			}, this));
		}

		/*if (widgetSettings.collapsible) {
			$('<a href="#" title="Collapse" class="collapse"></a>').mousedown(function (e) {
				e.stopPropagation();	
			}).toggle(function () {
				$(this).css({backgroundPosition: '0 100%'})
					.parents(this.settings.widgetSelector)
						.find(this.settings.contentSelector).hide();
				return false;
			},function () {
				$(this).css({backgroundPosition: ''})
					.parents(this.settings.widgetSelector)
						.find(this.settings.contentSelector).show();
				return false;
			}).appendTo($(this.settings.buttonsSelector,this));
		}*/

		// -------------------------------------------
		//  Show buttons on hover
		// -------------------------------------------

		$heading.hover($.proxy(function() {
			$buttons.show();
		}, this), $.proxy(function() {
			$buttons.hide();
		}, this));

	},

	/*
	 * Attach Stylesheet
	 */
	attachStylesheet : function (href) {
		return $('<link href="' + href + '" rel="stylesheet" type="text/css" />').appendTo('head');
	},

	/*
	 * Make Sortable
	 */
	makeSortable : function () {
		var $sortableItems = this.getWidgets();
			/*$sortableItems = (function () {
				var notSortable = '';
				$(this.settings.widgetSelector,$(this.settings.columnSelector)).each(function (i) {
					if (!dashEE.getWidgetSettings(this.id).movable) {
						if(!this.id) {
							this.id = 'widget-no-id-' + i;
						}
						notSortable += '#' + this.id + ',';
					}
				});
				return $('> li:not(' + notSortable + ')', this.settings.columnSelector);
			})();*/
			
		//$sortableItems.find(this.settings.headingSelector).css({
		$sortableItems.find(this.settings.headingSelector).css({
			cursor: 'move'
		}).mousedown($.proxy(function (e) {
			$sortableItems.css({width:''});
			$(e.currentTarget).parent().css({
				width: $(e.currentTarget).parent().width() + 'px'
			});
		}, this)).mouseup($.proxy(function (e) {
			if(!$(e.currentTarget).parent().hasClass('dragging')) {
				$(e.currentTarget).parent().css({width:''});
			} else {
				$(this.settings.columnSelector).sortable('disable');
			}
		}, this));

		$(this.settings.columnSelector).sortable({
			items: $sortableItems,
			connectWith: $(this.settings.columnSelector),
			handle: this.settings.headingSelector,
			placeholder: 'widget-placeholder',
			forcePlaceholderSize: true,
			revert: 300,
			delay: 100,
			opacity: 0.8,
			containment: 'document',
			start: $.proxy(function (e,ui) {
				$(ui.helper).addClass('dragging');
			}, this),
			stop: $.proxy(function (e,ui) {
				$(ui.item).css({width:''}).removeClass('dragging');
				$(this.settings.columnSelector).sortable('enable');

				var $widgets = this.getWidgets(),
					order = [];

				for (var i = 0; i < $widgets.length; i++) {
					var $widget = $($widgets[i]),
						col = parseInt($widget.parents('ul.column').attr('id').substr(-1));

					order.push(col+':'+$widget.attr('id'));
				};

				// save new order to DB
				$.ajax({
					type: 'GET',
					url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=update_widget_order',
					data: 'order='+order.join('|'),
					dataTyle: 'html',
					success: function(html) {
						$.ee_notice("Widget order updated.", {type: 'success'});
					},
					error: function(html) {
						$.ee_notice("ERROR: Unable to update widget order in DB.", {type: 'error', open: true});
					}
				});
			}, this)
		});
	}
  
};

$().ready(function() {
	// Override default breadcrumb display to make module look like default CP homepage.
	$('#breadCrumb ol li').slice(2).remove();
	$('#breadCrumb ol li:last-child').attr('class', 'last').html('Dashboard');
	
	// Click event to collapse all widgets.
	$('a[href="#collapse"]').click(function() {
		dashEE.getWidgets().addClass('collapsed');
	});
	
	// Click event to expand all widgets.
	$('a[href="#expand"]').click(function() {
		dashEE.getWidgets().removeClass('collapsed');
	});
	
	// Click event to display available widgets listing.
	$('a[href="#widgets"]').click(function() {
		if($('a[href="#widgets"]').html() == 'Widgets') {
			$('#dashListing .content').html('<p>&nbsp;</p><p><center>Loading...</center></p><p><center><img src="'+$('#dashLoader').attr('src')+'" /></center></p><p>&nbsp;</p>');
			$('#dashListing').slideDown();
			$.ajax({
				type: 'GET',
				url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=get_widget_listing',
				dataTyle: 'html',
				success: function(html) {
					$('#dashListing .content').html(html);
				},
				error: function(html) {
					$('#dashListing .content').html('<p>There was a problem.</p>');
				}
			});
			$('a[href="#widgets"]').html('Close');
		}
		else {
			$('#dashListing').slideUp();
			$('a[href="#widgets"]').html('Widgets');
		}
	}); 

	dashEE.init();
});

})(jQuery);
