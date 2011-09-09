/*
 * Script from NETTUTS.com [by James Padolsey] modified by Chris Monnat
 * @requires jQuery($), jQuery UI & sortable/draggable UI modules
 */

var url = window.location.href;
url = url.split('?')[0];

var dashEE = {
    
    jQuery : $,
    
    settings : {
        columns : '.column',
        widgetSelector: '.widget',
        handleSelector: '.widget-head',
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

    init : function () {
        this.addWidgetControls();
        this.makeSortable();
    },
    
    getWidgetSettings : function (id) {
        var $ = this.jQuery,
            settings = this.settings;
        return (id&&settings.widgetIndividual[id]) ? $.extend({},settings.widgetDefault,settings.widgetIndividual[id]) : settings.widgetDefault;
    },
    
    addWidgetControls : function () {
        var dashEE = this,
            $ = this.jQuery,
            settings = this.settings;
            
        $(settings.widgetSelector, $(settings.columns)).each(function () {
            var thisWidgetSettings = dashEE.getWidgetSettings($(this).attr('dashee'));
            if (thisWidgetSettings.removable) {
                $('<a href="#" title="close" class="remove">CLOSE</a>').mousedown(function (e) {
                    e.stopPropagation();    
                }).click(function () {
                	var thisLink = $(this);
                	var thisWidget = thisLink.parents('li.widget');
					var id = thisWidget.attr('id');
					var col = thisWidget.parents('ul.column').attr('id').substr(-1);
										
					$('#dashConfirm').dialog({
						resizable: false,
						height:140,
						modal: true,
						buttons: {
							'No': function() {
								$(this).dialog("close");
							},
							'Yes': function() {
								$.ajax({
									type: 'GET',
									url: url + '/?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=remove_widget&col='+col+'&wgt='+id,
									dataTyle: 'html',
									success: function(html) {
				                        thisLink.parents(settings.widgetSelector).animate({
				                            opacity: 0    
				                        },function () {
				                            $(this).wrap('<div/>').parent().slideUp(function () {
				                                $(this).remove();
				                            });
				                        });
										$.ee_notice("Widget has been removed.", {type: 'success'});
										setTimeout("$('#dashConfirm').dialog('close');", 750);
									},
									error: function(html) {
										$.ee_notice("ERROR: The widget you selected could not be removed.", {type: 'error', open: true});
									}
								});
							}
						},
						title: 'Remove Widget'
					});
                    return false;
                }).appendTo($(settings.handleSelector, this));
            }
            
            if (thisWidgetSettings.editable) {
                $('<a href="#" title="settings" class="edit">EDIT</a>').mousedown(function (e) {
                    e.stopPropagation();    
                }).click(function () {
                	var thisLink = $(this);
                	var thisWidget = thisLink.parents('li.widget');
                	var thisID = '#'+thisWidget.attr('id');
					var wgt = thisWidget.attr('id');
					var col = thisWidget.parents('ul.column').attr('id').substr(-1);
					
					$(thisID+' .widget-content').html('<p><center><img src="themes/third_party/dashee/images/ajax-loader.gif" /></center></p>');
					
					$.ajax({
						type: 'GET',
						url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=widget_settings&col='+col+'&wgt='+wgt,
						dataTyle: 'html',
						success: function(html) {
							$(thisID+' .widget-content').html(html);
							
							$('form.dashForm').submit(function(event) {
								event.preventDefault();
								$(thisID+' .widget-content').html('<p><center><img src="themes/third_party/dashee/images/ajax-loader.gif" /></center></p>');
			
								$.ajax({
									type: 'POST',
									url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=update_settings',
									data: $(this).serialize()+'&col='+col+'&wgt='+wgt,
									dataTyle: 'json',
									success: function(html) {
										var response = $.parseJSON(html);
										$(thisID+' .widget-head h3').html(response.title);
										$(thisID+' .widget-content').html(response.content);
									},
									error: function(html) {
										$(thisID+' .content').html('<p>There was a problem.</p>');
									}
								});
			
							});
						},
						error: function(html) {
							$(thisID+' .content').html('<p>There was a problem.</p>');
						}
					});
                }).appendTo($(settings.handleSelector,this));
            }
            
            if (thisWidgetSettings.collapsible) {
                $('<a href="#" title="collapse" class="collapse">COLLAPSE</a>').mousedown(function (e) {
                    e.stopPropagation();
                    
                    if($(this).parents(settings.widgetSelector).find(settings.contentSelector).is(':visible')) {
	                    $(this).css({backgroundPosition: '0 -15px'})
	                        .parents(settings.widgetSelector)
	                            .find(settings.contentSelector).hide();
                    }
                    else {
	                    $(this).css({backgroundPosition: ''})
	                        .parents(settings.widgetSelector)
	                            .find(settings.contentSelector).show();
                    }
                }).appendTo($(settings.handleSelector,this));
            }

            /*if (thisWidgetSettings.collapsible) {
                $('<a href="#" title="collapse" class="collapse">COLLAPSE</a>').mousedown(function (e) {
                    e.stopPropagation();    
                }).toggle(function () {
                    $(this).css({backgroundPosition: '0 -15px'})
                        .parents(settings.widgetSelector)
                            .find(settings.contentSelector).hide();
                    return false;
                },function () {
                    $(this).css({backgroundPosition: ''})
                        .parents(settings.widgetSelector)
                            .find(settings.contentSelector).show();
                    return false;
                }).appendTo($(settings.handleSelector,this));
            }*/

        });
        
		$('#dashContainer .widget-head').hover(
			function() {
				$(this).find('a.collapse').show();
				$(this).find('a.edit').show();
				$(this).find('a.remove').show();
			},
			function() {
				$(this).find('a.collapse').hide();
				$(this).find('a.edit').hide();
				$(this).find('a.remove').hide();
			}
		);
        
    },
    
    attachStylesheet : function (href) {
        var $ = this.jQuery;
        return $('<link href="' + href + '" rel="stylesheet" type="text/css" />').appendTo('head');
    },
    
    makeSortable : function () {
        var dashEE = this,
            $ = this.jQuery,
            settings = this.settings,
            $sortableItems = $(settings.widgetSelector);
            /*$sortableItems = (function () {
                var notSortable = '';
                $(settings.widgetSelector,$(settings.columns)).each(function (i) {
                    if (!dashEE.getWidgetSettings(this.id).movable) {
                        if(!this.id) {
                            this.id = 'widget-no-id-' + i;
                        }
                        notSortable += '#' + this.id + ',';
                    }
                });
                return $('> li:not(' + notSortable + ')', settings.columns);
            })();*/
            
		//$sortableItems.find(settings.handleSelector).css({
        $sortableItems.find(settings.handleSelector).css({
            cursor: 'move'
        }).mousedown(function (e) {
            $sortableItems.css({width:''});
            $(this).parent().css({
                width: $(this).parent().width() + 'px'
            });
        }).mouseup(function () {
            if(!$(this).parent().hasClass('dragging')) {
                $(this).parent().css({width:''});
            } else {
                $(settings.columns).sortable('disable');
            }
        });

        $(settings.columns).sortable({
            items: $sortableItems,
            connectWith: $(settings.columns),
            handle: settings.handleSelector,
            placeholder: 'widget-placeholder',
            forcePlaceholderSize: true,
            revert: 300,
            delay: 100,
            opacity: 0.8,
            containment: 'document',
            start: function (e,ui) {
                $(ui.helper).addClass('dragging');
            },
            stop: function (e,ui) {
                $(ui.item).css({width:''}).removeClass('dragging');
                $(settings.columns).sortable('enable');
            	
            	var i = 1;
				var widgets = '';
				var lastCol = '';
				$(settings.widgetSelector).each(function() {
					var col = parseInt($(this).parents('ul.column').attr('id').substr(-1));
					widgets += col+':'+$(this).attr('id');
					
					if(i < $(settings.widgetSelector).size()) {
						widgets += '|';
					}
					++i;
				});
							            
                // save new order to DB
                $.ajax({
					type: 'GET',
					url: url + '?D=cp&C=addons_modules&M=show_module_cp&module=dashee&method=update_widget_order',
					data: 'order='+widgets,
					dataTyle: 'html',
					success: function(html) {
						$.ee_notice("Widget order updated.", {type: 'success'});
					},
					error: function(html) {
						$.ee_notice("ERROR: Unable to update widget order in DB.", {type: 'error', open: true});
					}
                });
            }
        });
    }
  
};

$().ready(function() {
	// Override default breadcrumb display to make module look like default CP homepage.
	$('#breadCrumb ol li').slice(2).remove();
	$('#breadCrumb ol li:last-child').attr('class', 'last').html('Dashboard');
	
	// Click event to collapse all widgets.
	$('a[href="#collapse"]').click(function() {
		$(dashEE.settings.widgetSelector).each(function () {
			$(this).find('a.collapse').css({backgroundPosition: '0 -15px'});
			$(this).find(dashEE.settings.contentSelector).hide();
		});
	});
	
	// Click event to expand all widgets.
	$('a[href="#expand"]').click(function() {
		$(dashEE.settings.widgetSelector).each(function () {
			$(this).find('a.collapse').css({backgroundPosition: ''});
			$(this).find(dashEE.settings.contentSelector).show();
		});
	});
	
	// Click event to display available widgets listing.
	$('a[href="#widgets"]').click(function() {
		if($('a[href="#widgets"]').html() == 'Widgets') {
			$('#dashListing .content').html('<p>&nbsp;</p><p><center>Loading...</center></p><p><center><img src="themes/third_party/dashee/images/ajax-loader.gif" /></center></p><p>&nbsp;</p>');
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