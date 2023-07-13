/* globals jQuery, require */

window.cpGroupsFilter = window.cpGroupsFilter || {};

jQuery(($) => {

	$(document).on('click', function (e) {
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

	let modals = []

	function openModal( $modal ) {
		modals[0]?.dialog('close')

		modals = [ $modal, ...modals ]

		$modal.dialog('open')
	}

	function closeCurrentModal() {
		if( !modals.length ) return

		modals[0].dialog('close')
		modals.shift()
		modals[0]?.dialog('open')
	}

	const modalConfig = {
		title        : '',
		autoOpen     : false,
		draggable    : false,
		width        : 500,
		modal        : true,
		resizable    : false,
		closeOnEscape: true,
		position     : {
			my: 'center',
			at: 'bottom center',
			of: window
		},
		open() {
			$('.ui-widget-overlay').off('click');
			$('.ui-widget-overlay').on('click', closeCurrentModal);

			$(this).find('.cp-back-btn').off('click');
			$(this).find('.cp-back-btn').on('click', closeCurrentModal)
		}
	}

	// init for all items in group list
	const $groupItems = $('.cp-group-item')

	$groupItems.each(function() {
		const $this = $(this)

		const $detailsModal  = $this.find('.cp-group-modal')

		$detailsModal.dialog({
			...modalConfig,
			dialogClass: 'cp-groups-modal-popup'
		})

		$this.on('click', (e) => {
			if ($(e.target).hasClass('cp-button')) {
				return true;
			}

			e.preventDefault()

			openModal( $detailsModal )
		})

		initContactModals($detailsModal.find('.cp-group-single'))
	})

	// for a single group page
	const $groupSingle = $('.cp-pg-template .cp-group-single');

	$groupSingle.each(function() {
		initContactModals($(this))
	})

	function initContactModals($parent) {
		const $contactModal  = $parent.find('.cp-email-modal.action_contact')
		const $registerModal = $parent.find('.cp-email-modal.action_register')

		const contact  = new CP_Groups_Mail()
		const register = new CP_Groups_Mail()

		contact.init( $contactModal )
		register.init( $registerModal )
		contact.populate()
		register.populate()

		if( $contactModal.length ) {
			$contactModal.dialog({
				...modalConfig,
				dialogClass: 'cp-email-modal-action-contact cp-groups-modal-popup',
			})
		}

		if( $registerModal.length ) {
			$registerModal.dialog({
				...modalConfig,
				dialogClass: 'cp-email-modal-action-register cp-groups-modal-popup',
			})
		}

		const $registerButton = $parent.find('.cp-group-single--registration-url')
		const $contactButton  = $parent.find('.cp-group-single--contact-url')

		$registerButton.on('click', (e) => {
			if( $registerModal.length ) {
				e.preventDefault()
				openModal( $registerModal )
			}
		})

		$contactButton.on('click', (e) => {
			if( $contactModal.length ) {
				e.preventDefault()
				openModal( $contactModal )
			}
		})

	}
})

class CP_Groups_Mail {
	constructor() {
		this.$modal = null
		this.$form  = null

		this.requestError = this.requestError.bind(this)
		this.complete = this.complete.bind(this)
	}

	init($modal) {
		this.$modal = $modal
		this.submit()
	}

	submit() {
		this.$form = this.$modal.find('form');

		const $form = this.$form

		this.$modal.on('click', '.group-copy-email', function (e) {
			const email = $form.find('.email-to').val()

			let response = navigator.clipboard.writeText(email);
			response.finally(() => $(this).addClass('is-copied'));
		});

		this.$form.on('submit', async (e) => {
			e.preventDefault()

			const form = this.$form

			try {
				await this.before_submit( form )
			}
			catch(err) {
				this.message(err.message, 'error')
				return false;
			}

			form.ajaxSubmit({
				success     : this.success,
				complete    : this.complete,
				dataType    : 'json',
				error       : this.requestError,
			})
		})
	}

	before_submit = (form) => {
		form.find('.notice-wrap').remove();
		form.append('<div class="notice-wrap"><div class="update success"><p>Sending message.</p></div>');

		if(!window.recaptchaSiteKey) {
			return true
		}

		return new Promise((resolve, reject) => {
			grecaptcha.ready(() => {
				grecaptcha.execute(window.recaptchaSiteKey, { action: 'contact_group' } ).then((token) => {
					this.$form.prepend('<input type="hidden" name="token" value="' + token + '">');
					this.$form.prepend('<input type="hidden" name="action" value="contact_group">');
					resolve(true)
				})
				.catch(err => {
					reject(err)
				})
			})
		})
	}

	complete(xhr) {
		const self = jQuery(this),
			response = jQuery.parseJSON(xhr.responseText);

		if (response.success) {
			this.message(response.data.success, 'success')
		} else {
			this.requestError(xhr);
		}
	}

	clearFields() {
		this.$form.find('.from-name'    ).val('')
		this.$form.find('.email-from'   ).val('')
		this.$form.find('.subject'      ).val('')
		this.$form.find('[name=message]').val('')
	}

	success() {}

	requestError(xhr) {
		// Something went wrong. This will display error on form
		const response = jQuery.parseJSON(xhr.responseText);

		if (response.data.error) {
			this.message(response.data.error, 'error')
		} else {
			this.clearNotice
		}
	}

	populate() {
		const $data = this.$modal.find('[itemprop=groupDetails]')
		if( !$data.data('email') ) {
			return
		}

		let email;
		try {
			email = atob( $data.data('email') )
		}
		catch(err) {
			this.message( "An unexpected error occured" , "error")
		}

		this.$modal.find('.email-to').val(email)
	}

	message(text, type) {
		const import_form = this.$form;
		const notice_wrap = import_form.find('.notice-wrap');
		notice_wrap.html(`<div class="update ${type}"><p>${text}</p></div>`);
	}

	clearNotice() {
		const notice_wrap = this.$form.find('.notice-wrap');
		notice_wrap.remove()
	}
}
