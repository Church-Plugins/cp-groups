<?php
/**
 * Test block pattern
 *
 * @package CP_Groups
 */

return array(
	'title'      => __( 'Test', 'cp-groups' ),
	'blockTypes' => array( 'cp-groups/query' ),
	'categories' => array( 'posts' ),
	'content'    => '<!-- wp:cp-groups/query {"queryId":0,"query":{"perPage":3,"pages":0,"offset":0,"postType":"cp_group","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":null,"parents":[]}} -->
	<div class="wp-block-cp-groups-query"><!-- wp:cp-groups/group-template -->
	<!-- wp:cp-groups/group-title /-->
	<!-- /wp:cp-groups/group-template --></div>
	<!-- /wp:cp-groups/query -->',
);
