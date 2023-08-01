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
		return cmb2.groups_meta
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

	const blockProps = useBlockProps({
		className: 'cp-groups-badge'
	})

	const currentBadge = badgeTypes.find(badge => badge.value === badgeType)
	const showBadge = Boolean(badgeType && meta[badgeType] && currentBadge)

	return (
		<>
			<div {...blockProps}>
				{
					showBadge ?
					<>
						<span className='material-icons' dangerouslySetInnerHTML={{__html: currentBadge.icon}}></span>
						<div>{currentBadge.label}</div>
					</>
					:
					__( `This group is not ${currentBadge.label}` )
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
