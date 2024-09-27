/**
 * WordPress dependencies
 */
import { useSelect, withDispatch } from '@wordpress/data';
import {
	createBlocksFromInnerBlocksTemplate,
	store as blocksStore,
} from '@wordpress/blocks';
import {
	useBlockProps,
	store as blockEditorStore
} from '@wordpress/block-editor';
import { Button, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useBlockNameForPatterns } from '../utils';

const DEFAULT_TEMPLATE = [
	[ 'cp-groups/group-template' ]
];

function QueryPlaceholder( {
	attributes,
	clientId,
	name,
	openPatternSelectionModal,
	startBlank
} ) {

	const blockProps = useBlockProps();
	const blockNameForPatterns = useBlockNameForPatterns(
		clientId,
		attributes
	);

	const { blockType, hasPatterns } = useSelect(
		( select ) => {
			const { getBlockType } = select( blocksStore );
			const { getBlockRootClientId, getPatternsByBlockTypes } = select( blockEditorStore );
			const rootClientId = getBlockRootClientId( clientId );

			return {
				blockType: getBlockType( name ),
				hasPatterns: !! getPatternsByBlockTypes(
					blockNameForPatterns,
					rootClientId
				).length,
			};
		},
		[ name, blockNameForPatterns, clientId ]
	);

	const icon  = blockType?.icon?.src;
	const label = blockType?.title;

	return (
		<div { ...blockProps }>
			<Placeholder
				icon={ icon }
				label={ label }
				instructions={ __(
					'Choose a pattern for the query loop or start blank.'
				) }
			>
				{ !! hasPatterns && (
					<Button
						variant="primary"
						onClick={ openPatternSelectionModal }
					>
						{ __( 'Choose' ) }
					</Button>
				) }

				<Button
					variant="secondary"
					onClick={ startBlank }
				>
					{ __( 'Start blank' ) }
				</Button>
			</Placeholder>
		</div>
	);
}

export default withDispatch(( dispatch, { clientId } ) => {
	const { replaceInnerBlocks } = dispatch( blockEditorStore );

	return {
		startBlank: () => {
			replaceInnerBlocks(
				clientId,
				createBlocksFromInnerBlocksTemplate( DEFAULT_TEMPLATE ),
				false
			)
		}
	}
})(QueryPlaceholder)