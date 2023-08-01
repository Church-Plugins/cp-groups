/**
 * External dependencies
 */
import classnames from 'classnames';
/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { SelectControl, CheckboxControl } from '@wordpress/components'
import { PanelBody } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n'
/**
 * Internal dependencies
 */
import { useCanEditEntity } from '../utils/hooks';

function useAllTaxonomyTerms({ postType, postId }) {
	const taxonomies = useSelect(select => {
		const { getTaxonomies } = select( 'core' )
		return getTaxonomies( { type: postType } )
	}, [ postType ])

	return useSelect(select => {
		if(!taxonomies) return []
		return taxonomies.reduce((list, taxonomy) => {
			const taxonomyArgs = [
				'taxonomy',
				taxonomy.slug,
				{
					post: postId,
					per_page: -1,
					context: 'view',
				}
			];

			const newTerms = select('core').getEntityRecords(...taxonomyArgs) || []

			return {
				...list,
				[taxonomy.slug]: {
					...taxonomy,
					terms: newTerms
				}
			}
		}, {})
	}, [taxonomies])
}

export default function GroupTagsEdit( {
	context: { postType, postId, queryId },
	attributes: { primaryTagType, highlightStyle, additionalTagTypes },
	setAttributes
} ) {
	const isDescendentOfQueryLoop = Number.isFinite( queryId );
	const blockProps = useBlockProps({
		className: 'cp-group-item--categories'
	})

	const taxonomies = isDescendentOfQueryLoop ? useAllTaxonomyTerms({ postType, postId }) : {}

	console.log("Taxes", Object.entries(taxonomies))

	const primaryTags = taxonomies[primaryTagType]?.terms || []
	const additionalTags = Object.keys(taxonomies)
		.filter(termName => additionalTagTypes.includes(termName) && primaryTagType !== termName)
		.map(tagName => taxonomies[tagName].terms)
		.flat()

	return (
		<>
			<div {...blockProps}>
				{
					primaryTags.map(tag => (
						<div key={`${tag.taxonomy}-${tag.id}`}>
							<a href="#" className={classnames('cp-button', 'is-xsmall', {
							'is-transparent': highlightStyle === 'outline'
						})}>{tag.name}</a>
						</div>
					))
				}

				{
					additionalTags.map(tag => (
						<div key={`${tag.taxonomy}-${tag.id}`}>
							<a href="#" className={classnames('cp-button', 'is-xsmall', {
							'is-transparent': highlightStyle !== 'outline'
						})}>{tag.name}</a>
						</div>
					))
				}
			</div>
			<InspectorControls>
				<PanelBody title={ __( 'Tags settings' ) }>
					<SelectControl
							// multiple
							label={ __( 'Primary Tag Type:' ) }
							onChange={(tagType) => {
								setAttributes({ primaryTagType: tagType })
							}}
							value={primaryTagType}
							options={Object.entries(taxonomies).map(([slug, taxonomy]) => ({
								value: slug,
								label: taxonomy.name
							}))}
							__nextHasNoMarginBottom
					/>

					<SelectControl
							// multiple
							label={ __( 'Primary Tag Style:' ) }
							onChange={(highlightStyle) => {
								setAttributes({ highlightStyle })
							}}
							value={highlightStyle}
							options={[
								{	value: 'solid',   label: 'Solid'   },
								{ value: 'outline', label: 'Outline' }
							]}
							__nextHasNoMarginBottom
					/>

					<h3>{ __( "Tag types to display" ) }</h3>
					{
						Object.entries(taxonomies).map(([slug, taxonomy]) => (
							<CheckboxControl
								label={taxonomy.name}
								checked={additionalTagTypes.includes(slug) || primaryTagType === slug}
								disabled={primaryTagType === slug}
								onChange={(checked) => {
									setAttributes({
										additionalTagTypes: additionalTagTypes.filter(type => type !== slug)
									})
									if(checked) {
										setAttributes({
											additionalTagTypes: [...additionalTagTypes, slug]
										})
									}
								}}
							/>
						))
					}
					
				</PanelBody>
			</InspectorControls>
		</>
	);
}
