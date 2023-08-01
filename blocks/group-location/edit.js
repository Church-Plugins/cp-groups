/**
 * External dependencies
 */
import classnames from 'classnames';
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

	const blockProps = useBlockProps({
		className: 'cp-groups-location'
	})

	const cmb2 = useSelect(select => {
		const { cmb2 } = select('core').getEntityRecord( 'postType', postType, postId )

		console.log(cmb2)

		return cmb2
	})

	return (
		<>
			<div {...blockProps}>
				<span className='material-icons'>location_on</span>
				<div>{cmb2?.groups_meta?.location}</div>
			</div>
		</>
	);
}
