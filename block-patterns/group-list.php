<?php
/**
 * Group list block pattern
 *
 * @package CP_Groups
 */

return array(
	'title'      => __( 'Group List', 'cp-groups' ),
	'blockTypes' => array( 'cp-groups/query' ),
	'categories' => array( 'posts' ),
	'content'    => '<!-- wp:cp-groups/query {"queryId":0,"query":{"perPage":"10","pages":0,"offset":0,"postType":"cp_group","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"cp_group_category":[],"cp_group_type":[],"cp_group_life_stage":[]},"parents":[]},"displayLayout":{"type":"list","columns":3},"layout":{"type":"constrained","justifyContent":"left"}} -->
	<div class="wp-block-cp-groups-query"><!-- wp:cp-groups/group-template -->
	<!-- wp:columns {"style":{"spacing":{"margin":{"top":"0px","bottom":"32px"},"padding":{"top":"32px","left":"32px","right":"32px","bottom":"32px"},"blockGap":{"top":"0px","left":"32px"}},"color":{"background":"#f3f4f6"}}} -->
	<div class="wp-block-columns has-background" style="background-color:#f3f4f6;margin-top:0px;margin-bottom:32px;padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px"><!-- wp:column {"verticalAlignment":"top","width":"33.33%","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":"0"}}} -->
	<div class="wp-block-column is-vertically-aligned-top" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;flex-basis:33.33%"><!-- wp:cp-groups/group-featured-image {"aspectRatio":"16/9","width":"100%","style":{"layout":{"selfStretch":"fit","flexSize":null},"spacing":{"margin":{"bottom":"0px"}}}} /--></div>
	<!-- /wp:column -->
	
	<!-- wp:column {"verticalAlignment":"top","width":"66.66%"} -->
	<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:66.66%"><!-- wp:cp-groups/group-tags {"additionalTagTypes":["cp_group_type","cp_group_category"],"style":{"spacing":{"margin":{"bottom":"16px"}}}} /-->
	
	<!-- wp:cp-groups/group-title {"level":3,"isLink":true,"style":{"spacing":{"margin":{"top":"0px","left":"0px","right":"0px","bottom":"16px"}},"elements":{"link":{"color":{"text":"#334155"}}},"typography":{"textDecoration":"none","fontStyle":"normal","fontWeight":"600","fontSize":"26px"}}} /-->
	
	<!-- wp:cp-groups/group-time-desc {"style":{"spacing":{"margin":{"top":"0px","left":"0px","right":"0px","bottom":"0px"}},"typography":{"fontSize":"14px"}}} /-->
	
	<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
	<div class="wp-block-group"><!-- wp:cp-groups/group-badge {"style":{"color":{"text":"#ae7f00"},"typography":{"fontSize":"14px"}}} /-->
	
	<!-- wp:cp-groups/group-badge {"badgeType":"handicap_accessible","style":{"color":{"text":"#008f5a"},"typography":{"fontSize":"14px"}}} /--></div>
	<!-- /wp:group --></div>
	<!-- /wp:column --></div>
	<!-- /wp:columns -->
	<!-- /wp:cp-groups/group-template --></div>
	<!-- /wp:cp-groups/query -->',
);
