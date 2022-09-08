/* globals jQuery, require */

window.cpGroupsFilter = window.cpGroupsFilter || {};

jQuery(function ($) {
	
	$(document).ready(function() {
		
		let $groupItem = $('.cp-group-item');
		
		if ( ! $groupItem.length ) {
			return;
		}
		
		$groupItem.on('click', '.cp-group-item--title a, .cp-group-item--thumb',function(e) {
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
			});

			$modalElem.dialog('open');
			
		});
	} );
	
});
