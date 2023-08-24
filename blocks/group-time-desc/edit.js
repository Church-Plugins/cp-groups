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



export default function GroupTimeDescEdit( {
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
			opacity: cmb2?.groups_meta?.time_desc ? '1' : '0.5'
		}
	})

	return (
		<>
			<div {...blockProps}>
				<span className='material-icons'>calendar_today</span>
				<div>{cmb2?.groups_meta?.time_desc || __( 'No time specified', 'cp-groups' )}</div>
			</div>
		</>
	);
}
