/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
/**
 * WordPress dependencies
 */
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';

import { useSelect } from '@wordpress/data'
/**
 * Internal dependencies
 */
import { useCanEditEntity } from '../utils/hooks';


export default function GroupLocationEdit( {
	context: { postType, postId, queryId },
} ) {
	const isDescendentOfQueryLoop = Number.isFinite( queryId );

	const cmb2 = useSelect(select => {
		const { cmb2 } = select('core').getEntityRecord( 'postType', postType, postId )
		return cmb2
	})

	const blockProps = useBlockProps({
		className: 'cp-groups-location',
		style: {
			opacity: cmb2?.groups_meta?.location ? '1' : '0.5'
		}
	})

	return (
		<>
			<div {...blockProps}>
				<span className='material-icons'>location_on</span>
				<div>{cmb2?.groups_meta?.location || __( 'No location specified', 'cp-groups' )}</div>
			</div>
		</>
	);
}
