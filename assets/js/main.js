/* globals jQuery, require */

window.cpGroupsFilter = window.cpGroupsFilter || {};

jQuery(function ($) {
	
	$(document).ready(function() {} );
	
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
