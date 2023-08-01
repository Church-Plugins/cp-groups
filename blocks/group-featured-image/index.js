/**
 * WordPress dependencies
 */
import { postFeaturedImage as icon } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './edit';

registerBlockType(metadata, { edit, icon })