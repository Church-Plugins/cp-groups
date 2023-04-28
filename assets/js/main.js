/* globals jQuery, require */

window.cpGroupsFilter = window.cpGroupsFilter || {};

jQuery(function ($) {

	$(document).ready(function () {

		let $groupItem = $('.cp-group-item');

		if (!$groupItem.length) {
			return;
		}

		$groupItem.on('click', function (e) {
			if ($(e.target).hasClass('cp-button')) {
				return true;
			}

			e.preventDefault();

			let $this = $(this);
			let $modalElem = $this.find('.cp-group-modal').clone();

			$modalElem.dialog({
				title        : '',
				dialogClass  : 'cp-groups-modal-popup',
				autoOpen     : false,
				draggable    : false,
				width        : 500,
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

					$(event.target).dialog('widget')
						.css({position: 'fixed'})
						.position({my: 'center', at: 'center', of: window});

				},
			});

			$modalElem.dialog('open');

		});

		$(document).click(function (e) {
			var $dropdown = $('.cp-groups-filter--has-dropdown');

			if (!$(e.target).closest($dropdown).length) {
				$dropdown.removeClass('open');
			}
		});

		$('.cp-groups-filter--toggle--button').on('click', function (e) {
			e.preventDefault();
			$('.cp-groups-filter--has-dropdown').toggle();
		});

		$('.cp-groups-filter--form input[type=checkbox]').on('change', function () {
			$('.cp-groups-filter--form').submit();
		});

		$('.cp-groups-filter--has-dropdown a').on('click', function (e) {
			e.preventDefault();
			$(this).parent().toggleClass('open');
		});
	});

});
