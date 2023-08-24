/**
 * External dependencies
 */
import classnames from 'classnames';
/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components'
import { useSelect } from '@wordpress/data'
import { __ } from '@wordpress/i18n'
/**
 * Internal dependencies
 */
import { useCanEditEntity } from '../utils/hooks';


export default function GroupBadge( {
	context: { postType, postId, queryId },
	attributes: { badgeType },
	setAttributes
} ) {
	const isDescendentOfQueryLoop = Number.isFinite( queryId );

	const meta = useSelect(select => {
		const { cmb2 } = select('core').getEntityRecord( 'postType', postType, postId )
		return cmb2?.groups_meta || {}
	})

	const badgeTypes = [
		{
			label: __( 'Kid Friendly', 'cp-groups' ),
			value: 'kid_friendly',
			icon: 'escalator_warning'
		},
		{
			label: __( 'Accessible', 'cp-groups' ),
			value: 'handicap_accessible',
			icon: 'accessible'
		}
	]

	const currentBadge = badgeType ? badgeTypes.find(badge => badge.value === badgeType) : badgeTypes[0]
	const showBadge = Boolean(badgeType && meta[badgeType] && currentBadge)

	const blockProps = useBlockProps({
		className: 'cp-groups-badge',
		style: {
			opacity: showBadge ? '1' : '0.5'
		}
	})



	return (
		<>
			<div {...blockProps}>
				{
					!badgeType ?
					<div>{ __( 'Select a badge', 'cp-library' ) }</div> :
					<>
						<span className='material-icons' dangerouslySetInnerHTML={{__html: currentBadge.icon}}></span>
						<div>{showBadge ? currentBadge.label : __( 'No badge', 'cp-library' ) }</div>
					</>
				}
				
			</div>
			<InspectorControls>
				<PanelBody title={ __( 'Badge Settings', 'cp-groups' ) }>
					<SelectControl
						label={ __( 'Badge Type' ) }
						value={ badgeType }
						onChange={(badgeType) => {
							setAttributes({ badgeType })
						}}
						options={badgeTypes}
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
}
