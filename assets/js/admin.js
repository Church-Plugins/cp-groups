/**
 * Scripts that run in the CP Groups admin.
 */

/**
 * Group leader field interaction.
 *
 * @since 1.2.0
 */
jQuery($ => {
	const manageField = (field) => {
		const template = field.querySelector('template')
		const list     = field.querySelector('.cp-groups--group-leader-list')

		field.querySelector('.cp-groups--add-leader').addEventListener('click', () => {
			const clone = template.content.cloneNode(true)
			list.appendChild(clone)
			manageFieldRow(list.lastElementChild)
			updateIndices(list)
		})

		field.querySelectorAll('.cp-groups--group-leader').forEach(manageFieldRow)
	}

	const updateIndices = (list) => {
		list.querySelectorAll('.cp-groups--group-leader').forEach((row, index) => {
			row.querySelector('.cp-groups--leader-select').name = `leaders[${index}][id]`
			row.querySelector('.cp-groups--leader-name').name = `leaders[${index}][name]`
			row.querySelector('.cp-groups--leader-email').name = `leaders[${index}][email]`
		})
	}

	const manageFieldRow = (row) => {
		row.querySelector('.cp-groups--remove-leader').addEventListener('click', () => {
			row.remove()
			updateIndices(row.parentElement)
		})

		row.querySelector('.cp-groups--leader-select').addEventListener('change', (e) => {
			if(e.target.value === '') {
				row.querySelector('.cp-groups--leader-name').style.display = 'block'
				row.querySelector('.cp-groups--leader-email').style.display = 'block'
			} else {
				row.querySelector('.cp-groups--leader-name').style.display = 'none'
				row.querySelector('.cp-groups--leader-email').style.display = 'none'
			}
		})
	}

	document.querySelectorAll('.cp-groups--group-leader-field').forEach(manageField)	
})
