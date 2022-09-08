/* globals jQuery, require */

window.cpGroupsFilter = window.cpGroupsFilter || {};

jQuery(function ($) {
	
	$(document).ready(function() {
		$('.cp-group-item').on('click', '.cp-group-item--title a, .cp-group-item--thumb',function(e) {
			e.preventDefault();
			
			let $this = $(this);
			let $modalElem = $this.parents('.cp-group-item').find('.cp-group-modal').clone();
	
			$modalElem.dialog({
				title        : '',
				dialogClass  : 'groups-modal-popup',
				autoOpen     : false,
				draggable    : false,
				width        : 'auto',
				modal        : true,
				resizable    : false,
				closeOnEscape: true,
				position     : {
					my: 'center',
					at: 'center',
					of: window
				},
				open         : function () {
					// close dialog by clicking the overlay behind it
					$('.ui-widget-overlay').bind('click', function () {
						$modalElem.dialog('close');
					});

				},
//				close        : function () {
//					history.replaceState(null, null, '/integrations/');
//					document.title = 'SmartVault Integration Center - Accounting, Tax, e-Signature, Fetching and More';
//				}
			});

			$modalElem.dialog('open');
			
		});
	} );
	
	var groupsFilter = function() {
		var SELF = this;
		
		SELF.init = function() {
			
			SELF.$filterCont = $('.cp-groups-filter');
			
			if ( ! SELF.$filterCont.length ) {
				return;
			}

			SELF.$archive = $('.cp-groups-archive');
			SELF.$form = SELF.$filterCont.find('form');
			
			SELF.$form.on('submit', 'handleFilter');
		};
		
		SELF.handleFilter = function(e) {
			e.preventDefault();
		}

	}

	window.cpGroupsFilter = new groupsFilter();
	$(document).ready( function(){
		window.cpGroupsFilter.init();
	});
	
});
