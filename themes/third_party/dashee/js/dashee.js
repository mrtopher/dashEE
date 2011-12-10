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
				col = widget.parents(this.settings).attr('id').substr(-1);
				
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
				col = widget.parents(this.settings).attr('id').substr(-1);
				
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
								url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=update_widget_settings',
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
	$('a[href="#collapse"]').parent('.button').css('float', 'left');
	$('a[href="#expand"]').parent('.button').css('float', 'left');
	//$('a[href="#save-layout"]').parent('.button').hide();

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
			$('a[href="#widgets"]').html('Close Widgets');
		}
		else {
			$('#dashListing').slideUp();
			$('a[href="#widgets"]').html('Widgets');
		}
	}); 
	
	// Click event to save current widget layout to DB.
	$('a[href="#save-layout"]').click($.proxy(function (e) {
		$('#dashSaveLayout').dialog({
			resizable: false,
			height:210,
			width:350,
			modal: true,
			buttons: {
				'Cancel': function() {
					$(this).dialog("close");
				},
				'Save': $.proxy(function() {
					$.ajax({
						type: 'POST',
						url: url + '/?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=save_layout',
						data: $('#dashSaveLayout form').serialize(),
						dataTyle: 'html',
						success: $.proxy(function(html) {
							$.ee_notice('Your layout has been saved. Click "Settings" to assign it to a member group.', {type: 'success', open: true});
							$('#dashSaveLayout').dialog('close');
						}, this),
						error: $.proxy(function(html) {
							$.ee_notice("ERROR: The widget you selected could not be removed.", {type: 'error', open: true});
						}, this)
					});
				}, this)
			},
			title: 'Save Layout'
		});
		return false;
	}, this));
	
	// Click event to save current widget layout to DB.
	$('a.dashLoad').click(function (e) {
		e.preventDefault();
		href = $(this).attr('href');
		$('#dashConfirmLoad').dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				'Cancel': function() {
					$(this).dialog("close");
				},
				'OK': function() {
					$(this).dialog("close");
					window.location = href;
				}
			},
			title: 'Please Confirm'
		});
	});
	
	// Click event to save current widget layout to DB.
	$('a.dashDelete').click(function (e) {
		e.preventDefault();
		href = $(this).attr('href');
		$('#dashConfirmDelete').dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				'No': function() {
					$(this).dialog("close");
				},
				'Yes': function() {
					$(this).dialog("close");
					window.location = href;
				}
			},
			title: 'Please Confirm'
		});
	});
	
	// Click event to display settings help.
	$('a.dashHelp').click(function() {
		$('.dashLayoutHelp').dialog({
			resizable: false,
			width:450,
			modal: true,
			title: 'dashEE Layouts'
		});
	});
	
	dashEE.init();
});

})(jQuery);
