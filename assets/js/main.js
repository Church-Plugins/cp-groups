/* globals jQuery, require */

window.cpGroupsFilter = window.cpGroupsFilter || {};





jQuery(($) => {

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

	function populateModal($modal) {

	}

	const modalConfig = {
		title        : '',
		autoOpen     : false,
		draggable    : false,
		width        : 'min(calc(100vw - var(--cp-gap--lg)), 500px)',
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

	const $groupItems = $('.cp-group-item')

	if( !$groupItems.length ) return

	$groupItems.each(function() {
		const $this = $(this)
		const $contactModal  = $this.find('.cp-email-modal.action_contact')
		const $registerModal = $this.find('.cp-email-modal.action_register')
		const $detailsModal  = $this.find('.cp-group-modal')

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

		$detailsModal.dialog({
			...modalConfig,
			dialogClass: 'cp-groups-modal-popup'
		})

		const $registerButton = $detailsModal.find('.cp-group-single--registration-url')
		const $contactButton  = $detailsModal.find('.cp-group-single--contact-url')

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

		$this.on('click', (e) => {
			if ($(e.target).hasClass('cp-button')) {
				return true;
			}

			e.preventDefault()

			openModal( $detailsModal )
		})
	})
})

class CP_Groups_Mail {
	constructor() {
		this.$modal = null
		this.$form  = null

		// this.success = this.success.on(this)
		this.requestError = this.requestError.on(this)
		this.complete = this.complete.on(this)
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
			email = atob( '$@#R@#$(8' );
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
