/**
 * WordPress dependencies
 */
import { people as icon } from '@wordpress/icons';
import { addFilter } from '@wordpress/hooks';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './edit';
import save from './save';
import queryInspectorControls from './hooks';

registerBlockType(metadata, { edit, save, icon })

addFilter( 'editor.BlockEdit', 'cp-groups/query', queryInspectorControls )

// allows the pagination block to be used with the groups query block
addFilter(
	'blocks.registerBlockType',
	'cp-groups/query-pagination',
	function( settings, name ) {
		if ( 'core/query-pagination' === name ) {
			return {
				...settings,
				parent: [ ...(settings.parent || []), 'cp-groups/query' ]
			}
		}
	
		return settings
	}
);
