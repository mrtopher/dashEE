/* Main dashEE Module JS File */
$(function() {

	var $config_id = $('#config_id').val();
	var dash = {
		
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
		
		init : function() {
			$(this.settings.widgetSelector).each(function() {
				dash.initWidget($(this));			
			});
	
			dash.makeSortable();
		},
		
		initWidget : function(widget) {
			var wgt = {
				heading : $(dash.settings.headingSelector, widget),
				buttons : $(dash.settings.buttonsSelector, widget),
				content : $(dash.settings.contentSelector, widget),
				id : widget.attr('id'),
				settings : dash.getWidgetSettings(widget.attr('dashee'))
				};
			
			//  Remove button	
			if(wgt.settings.removable) {
				dash.removeWidget(widget, wgt);
			}
	
			//  Settings button	
			if(wgt.settings.editable) {
				dash.updateWidget(widget, wgt);
			}
	
			//  Collapse button	
			if (wgt.settings.collapsible) {
				dash.collapseWidget(widget, wgt);
			}
	
			//  Show buttons on hover	
			wgt.heading.hover($.proxy(function() {
				wgt.buttons.show();
			}, this), $.proxy(function() {
				wgt.buttons.hide();
			}, this));
		},
		
		getWidgets : function() {
			return $(this.settings.widgetSelector);
		},
		
		getWidgetSettings : function(id) {
			var settings = (id && this.settings.widgetIndividual[id]) ? this.settings.widgetIndividual[id] : {};
			return $.extend({}, this.settings.widgetDefault, settings);
		},
		
		addWidget : function(link) {
			link.html('<img src="'+$('#widgetLoader').attr('src')+'" />');
			
			$.ajax({
				type: 'GET',
				url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_add_widget&mod='+link.data('module')+'&wgt='+link.data('widget')+'&config_id='+$config_id,
				dataType: 'html',
				success: $.proxy(function(json) {
					widget = $.parseJSON(json);
					$rwidget = $(widget.html);
					$rwidget.appendTo('#column'+widget.col);
					
					dash.initWidget($rwidget);
					dash.makeSortable();
				
					link.html('Add');
					$.ee_notice("Widget added.", {type: 'success'});
				}, this),
				error: $.proxy(function(html) {
					$.ee_notice("ERROR: The widget you selected could not be added.", {type: 'error', open: true});
				}, this)
			});
		},
		
		removeWidget : function(widget, wgt) {
			var $button = $('<a href="#" title="Remove" class="remove"></a>').appendTo(wgt.buttons);

			$button.mousedown($.proxy(function (e) {
				e.stopPropagation();	
			}, this));

			$button.click($.proxy(function (e) {
				$('#dashConfirm').dialog({
					resizable: false,
					height:140,
					modal: true,
					buttons: {
						'Yes': $.proxy(function() {
							$('.dialog-remove-widget > .ui-dialog-content').html('<p><center>Please wait...<br /><img src="'+$('#dashLoader').attr('src')+'" /></center></p>');
							$('.dialog-remove-widget > .ui-dialog-buttonpane').hide();
							$.ajax({
								type: 'GET',
								url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_remove_widget&wgt='+wgt.id+'&config_id='+$config_id,
								dataType: 'html',
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
						}, this),
						'No': function() {
							$(this).dialog("close");
						}
					},
					close : function() {
						$('.dialog-remove-widget > .ui-dialog-content').html('<p>Are you sure you want to remove this widget from your dashboard?</p>');
						$('.dialog-remove-widget > .ui-dialog-buttonpane').show();
					},
					dialogClass: 'dialog-remove-widget',
					title: 'Remove Widget'
				});
				return false;
			}, this));
		},
		
		updateWidget : function(widget, wgt) {
			var $button = $('<a href="#" title="Settings" class="edit"></a>').appendTo(wgt.buttons);

			$button.mousedown($.proxy(function (e) {
				e.stopPropagation();
			}, this));

			$button.click($.proxy(function () {
				wgt.content.html('<p><center><img src="'+$('#dashLoader').attr('src')+'" /></center></p>');

				if(wgt.content.hasClass('settings')) {
					wgt.content.removeClass('settings');

					$.ajax({
						type: 'GET',
						url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_get_widget&wgt='+wgt.id+'&config_id='+$config_id,
						dataType: 'html',
						success: $.proxy(function(html) {
							var response = $.parseJSON(html);
							$('h2', wgt.heading).html(response.title);
							wgt.content.html(response.content);
						}, this),
						error: $.proxy(function(html) {
							wgt.content.html('<p>There was a problem.</p>');
						}, this)
					});
				} 
				else { 
					$.ajax({
						type: 'GET',
						url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_widget_settings&wgt='+wgt.id+'&config_id='+$config_id,
						dataType: 'html',
						success: $.proxy(function(html) {
							wgt.content.html(html);
							wgt.content.addClass('settings');
	
							$('form.dashForm').submit(function(event) {
								event.preventDefault();
								wgt.content.removeClass('settings');
								wgt.content.html('<p><center><img src="' + $('#dashLoader').attr('src') + '" /></center></p>');
			
								$.ajax({
									type: 'POST',
									url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_update_widget_settings&config_id='+$config_id,
									data: $(this).serialize() + '&wgt=' + wgt.id,
									success: function(html) {
										var response = $.parseJSON(html);
										$('h2', wgt.heading).html(response.title);
										wgt.content.html(response.content);
									},
									error: function(html) {
										wgt.content.html('<p>There was a problem.</p>');
									}
								});
							});
						}, this),
						error: $.proxy(function(html) {
							wgt.content.html('<p>There was a problem.</p>');
						}, this)
					});
				};
			}, this));
		},
		
		collapseWidget : function(widget, wgt) {
			var $button = $('<a href="#" title="Collapse" class="collapse"></a>').appendTo(wgt.buttons);

			$button.mousedown($.proxy(function (e) {
				widget.toggleClass('collapsed');

				var $state = 1;
				if(widget.hasClass('collapsed'))
				{
					$state = 0;
				}

				$.ajax({
					type: 'GET',
					url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_update_widget_state&state='+$state+'&wgt='+wgt.id+'&config_id='+$config_id
					// success: function(html) {
					// 	$.ee_notice('Widget state has been saved.', {type: 'success', open: true});
					// },
					// error: function(html) {
					// 	$.ee_notice('There was a prpoblem.', {type: 'error', open: true});
					// }
				});
			}, this));
		},
		
		makeSortable : function() {
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
	
					$(this.settings.widgetSelector).each(function() {
						var $widget = $(this),
							col = parseInt($widget.parents('ul.column').attr('id').substr(-1));
	
						order.push(col+':'+$widget.attr('id'));
					});
	
					// save new order to DB
					$.ajax({
						type: 'GET',
						url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_update_widget_order&config_id='+$config_id,
						data: 'order='+order.join('|'),
						dataType: 'html',
						success: function(html) {
							$.ee_notice("Widget order updated.", {type: 'success'});
						},
						error: function(html) {
							$.ee_notice("ERROR: Unable to update widget order in DB.", {type: 'error', open: true});
						}
					});
				}, this)
			});
		},
		
		dialog : function(type, txtid, href, boxtitle, boxwidth, boxheight) {
			if(type == 'confirm') {
				buttons = {
					'OK': function() {
						$(this).dialog("close");
						window.location = href;
					},
					'Cancel': function() {
						$(this).dialog("close");
					}
				};
				
				boxtitle = 'Please Confirm';
			} else {
				buttons = {
					'OK': function() {
						$(this).dialog("close");
					}
				};
			}

			if(boxwidth == '') {
				boxwidth = 250;
			}
		
			if(boxheight == '') {
				boxheight = 140;
			}

			$(txtid).dialog({
				resizable: false,
				width:boxwidth,
				height:boxheight,
				modal: true,
				buttons: buttons,
				title: boxtitle
			});			
		}
		
	};
	
	// Click event to collapse all widgets.
	$('a[href="#collapse"]').on('click', function() {
		dash.getWidgets().addClass('collapsed');

		$.ajax({
			type: 'GET',
			url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_update_widget_state&state=0&config_id='+$config_id
/*			success: function(html) {
				$.ee_notice('Widget state has been saved.', {type: 'success', open: true});
			},
			error: function(html) {
				$.ee_notice('There was a prpoblem.', {type: 'error', open: true});
			}
*/		});
	});

	// Click event to expand all widgets.
	$('a[href="#expand"]').on('click', function() {
		dash.getWidgets().removeClass('collapsed');

		$.ajax({
			type: 'GET',
			url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_update_widget_state&state=1&config_id='+$config_id
/*			success: function(html) {
				$.ee_notice('Widget state has been saved.', {type: 'success', open: true});
			},
			error: function(html) {
				$.ee_notice('There was a prpoblem.', {type: 'error', open: true});
			}
*/		});
	});
	
	// Click event for adding new widgets.
	$('#dashListing').on('click', 'a.addWidget', function(e) {
		e.preventDefault();
		dash.addWidget($(this));
	});
	
	// Click event to display "load layout" confirmation message.
	$('a.dashLoad').on('click', function(e) {
		e.preventDefault();
		dash.dialog('confirm', '#dashConfirmLoad', $(this).attr('href'), '', '', 140);
	});
	
	// Click event to display "delete layout" confirmation message.
	$('a.dashDelete').click(function (e) {
		e.preventDefault();
		dash.dialog('confirm', '#dashConfirmDelete', $(this).attr('href'), '', '', 140);
	});
	
	// Click event to display "reset layout" confirmation message.
	$('a.dashReset').click(function (e) {
		e.preventDefault();
		dash.dialog('confirm', '#dashConfirmReset', $(this).attr('href'), '', '', 190);
	});
	
	// Click event to display settings help.
	$('a.dashLayoutHelp').click(function() {
		dash.dialog('help', '#dashLayoutHelp', '', 'dashEE Layouts', 450, 340);
	});
	
	// Click event to display layout locking help.
	$('a.dashLockHelp').click(function() {
		dash.dialog('help', '#dashLockHelp', '', 'Lock Layouts', 310, 190);
	});
	
	// Click event to display available widgets listing.
	$('a[href="#widgets"]').on('click', function() {
		if($('#dashListing').is(':hidden')) {
			$('#dashListing .widgets').html('<p>&nbsp;</p><p><center>Loading...</center></p><p><center><img src="'+$('#dashLoader').attr('src')+'" /></center></p><p>&nbsp;</p>');
			$('#dashListing').slideDown();
			$.ajax({
				type: 'GET',
				url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_get_widget_listing&config_id='+$config_id,
				dataType: 'html',
				success: function(html) {
					$('#dashListing .widgets').html(html);
				},
				error: function(html) {
					$('#dashListing .widgets').html('<p>There was a problem: ' + html + '</p>');
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
	$('a[href="#save-layout"]').on('click', $.proxy(function (e) {
		$('#dashSaveLayout').dialog({
			resizable: false,
			height:210,
			width:350,
			modal: true,
			buttons: {
				'Save': $.proxy(function() {
					$('<span class="loading"><center>Please wait...<br /><img src="'+$('#dashLoader').attr('src')+'" /></center></span>').appendTo('.dialog-save-layout > .ui-dialog-content');
					$('#dasheeLayoutForm').hide();
					$('.dialog-save-layout > .ui-dialog-buttonpane').hide();
					$.ajax({
						type: 'POST',
						url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_save_layout&config_id='+$config_id,
						data: $('#dasheeLayoutForm').serialize(),
						dataType: 'html',
						success: $.proxy(function(html) {
							$.ee_notice('Your layout has been saved. Click "Settings" to assign it to a member group.', {type: 'success', open: true});
							$('#dashSaveLayout').dialog('close');
						}, this),
						error: $.proxy(function(html) {
							$.ee_notice("ERROR: The widget you selected could not be removed.", {type: 'error', open: true});
						}, this)
					});
				}, this),
				'Cancel': function() {
					$(this).dialog("close");
				}
			},
			close : function() {
				$('.dialog-save-layout > .ui-dialog-content .loading').remove();
				$('#dasheeLayoutForm').show();
				$('#dasheeLayoutForm input').val('');
				$('.dialog-save-layout > .ui-dialog-buttonpane').show();
			},
			dialogClass: 'dialog-save-layout',
			title: 'Save Dashboard Layout'
		});
		return false;
	}, this));

	// Click event to display member settings dialog.
	$('a[href="#member-settings"]').on('click', $.proxy(function (e) {
		$('#dashMemberSettings').dialog({
			resizable: false,
			height:210,
			width:350,
			modal: true,
			buttons: {
				'Save': $.proxy(function() {
					$('<span class="loading"><center>Please wait...<br /><img src="'+$('#dashLoader').attr('src')+'" /></center></span>').appendTo('.dialog-member-settings > .ui-dialog-content');
					$('#dasheeMemberSettingsForm').hide();
					$('.dialog-member-settings > .ui-dialog-buttonpane').hide();

					$('form#dasheeMemberSettingsForm').submit();
				}, this),
				'Cancel': function() {
					$(this).dialog("close");
				}
			},
			dialogClass: 'dialog-member-settings',
			title: 'Display Settings'
		});
		return false;
	}, this));

	// Event to handle widget form submissions.
	$(document).on('submit', '.wgt_form', function (e) {
		e.preventDefault();

		var $widget = $(this).parents('li');
		var $widget_id = $widget.attr('id');

		$.ajax({
			type: 'POST',
			url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_widget_post_proxy&config_id='+$config_id,
			data: $(this).serialize() + '&wgtid=' + $widget_id,
			success: function(html) {
				var $result = $.parseJSON(html);

				if($result.type == 'success') {
					$('h2', $widget).html($result.title);
					$widget.find('.widget-content').html($result.content);
					$.ee_notice($result.message, {type: 'success', open: false});
				}
				else {
					$.ee_notice($result.message, {type: 'error', open: true});
				}
			},
			error: function(html) {
				$.ee_notice("Nope, there was a problem.", {type: 'error', open: true});
			}
		});
	});

	// Click event to display rename dashboard dialog.
	$('a[href="#rename-dashboard"]').on('click', $.proxy(function (e) {
		$('#dashRenameDashboard input[name="dashboard_name"]').val($('#dashboards a.active').html());

		$('#dashRenameDashboard').dialog({
			resizable: false,
			height:150,
			width:350,
			modal: true,
			buttons: {
				'Save': $.proxy(function() {
					$('<span class="loading"><center>Please wait...<br /><img src="'+$('#dashLoader').attr('src')+'" /></center></span>').appendTo('.dialog-rename-dashboard > .ui-dialog-content');
					$('#dasheeRenameDashboardForm').hide();
					$('.dialog-rename-dashboard > .ui-dialog-buttonpane').hide();

					$('form#dasheeRenameDashboardForm').submit();
				}, this),
				'Cancel': function() {
					$(this).dialog("close");
				}
			},
			dialogClass: 'dialog-rename-dashboard',
			title: 'Rename Dashboard'
		});
		return false;
	}, this));

	// Click event to display new dashboard dialog.
	$('a[href="#new-dashboard"]').on('click', $.proxy(function (e) {
		$('#dashNewDashboard').dialog({
			resizable: false,
			height:210,
			width:350,
			modal: true,
			buttons: {
				'Create': $.proxy(function() {
					$('<span class="loading"><center>Please wait...<br /><img src="'+$('#dashLoader').attr('src')+'" /></center></span>').appendTo('.dialog-new-dashboard > .ui-dialog-content');
					$('#dasheeNewDashboardForm').hide();
					$('.dialog-new-dashboard > .ui-dialog-buttonpane').hide();

					$('form#dasheeNewDashboardForm').submit();
				}, this),
				'Cancel': function() {
					$(this).dialog("close");
				}
			},
			dialogClass: 'dialog-new-dashboard',
			title: 'Create New Dashboard'
		});
		return false;
	}, this));
		
	dash.init();
}); 