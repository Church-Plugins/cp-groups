/**
 * WordPress dependencies
 */
import { postExcerpt as icon } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './edit';

registerBlockType(metadata, { icon, edit })