<?php
/**
 * Group list block pattern
 *
 * @package CP_Groups
 */

return array(
	'title'      => __( 'Group List Alt', 'cp-groups' ),
	'blockTypes' => array( 'cp-groups/query' ),
	'categories' => array( 'posts' ),
	'content'    => '<!-- wp:cp-groups/query {"queryId":0,"query":{"perPage":"10","pages":0,"offset":0,"postType":"cp_group","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"cp_group_category":[],"cp_group_type":[],"cp_group_life_stage":[]},"parents":[]},"displayLayout":{"type":"list","columns":3},"layout":{"type":"constrained","justifyContent":"left"}} -->
	<div class="wp-block-cp-groups-query"><!-- wp:cp-groups/group-template -->
	<!-- wp:columns {"style":{"spacing":{"margin":{"top":"0px","bottom":"32px"},"padding":{"top":"32px","left":"32px","right":"32px","bottom":"32px"},"blockGap":{"top":"0px","left":"32px"}},"color":{"background":"#334155"}}} -->
	<div class="wp-block-columns has-background" style="background-color:#334155;margin-top:0px;margin-bottom:32px;padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px"><!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%"><!-- wp:group {"style":{"spacing":{"margin":{"top":"0px","bottom":"0"},"padding":{"right":"0","left":"0","top":"0","bottom":"0"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
	<div class="wp-block-group" style="margin-top:0px;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cp-groups/group-tags {"highlightStyle":"outline","additionalTagTypes":["cp_group_type","cp_group_category"],"style":{"spacing":{"margin":{"bottom":"0px","top":"0px"}}}} /--></div>
	<!-- /wp:group -->
	
	<!-- wp:cp-groups/group-title {"textAlign":"left","level":3,"isLink":true,"style":{"elements":{"link":{"color":{"text":"#cbd5e1"}}},"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"32px","textDecoration":"none"},"spacing":{"margin":{"top":"16px","right":"0px","bottom":"0px","left":"0px"}}},"textColor":"ast-global-color-5"} /-->
	
	<!-- wp:group {"style":{"spacing":{"margin":{"top":"15px","bottom":"0"},"blockGap":"8px","padding":{"right":"0","left":"0","top":"0","bottom":"0"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
	<div class="wp-block-group" style="margin-top:15px;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cp-groups/group-time-desc {"style":{"spacing":{"margin":{"left":"0px","right":"0px","bottom":"0px","top":"0px"}},"typography":{"fontSize":"14px"}},"textColor":"white"} /-->
	
	<!-- wp:cp-groups/group-location {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"textColor":"white"} /--></div>
	<!-- /wp:group -->
	
	<!-- wp:group {"style":{"spacing":{"blockGap":"8px","margin":{"top":"16px","bottom":"0"},"padding":{"right":"0","left":"0","top":"0","bottom":"0"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
	<div class="wp-block-group" style="margin-top:16px;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cp-groups/group-badge {"style":{"color":{"text":"#cda024"},"typography":{"fontSize":"14px"}}} /-->
	
	<!-- wp:cp-groups/group-badge {"badgeType":"handicap_accessible","style":{"color":{"text":"#18b27a"},"typography":{"fontSize":"14px"}}} /--></div>
	<!-- /wp:group --></div>
	<!-- /wp:column -->
	
	<!-- wp:column {"verticalAlignment":"top","width":"50%","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":"0"}}} -->
	<div class="wp-block-column is-vertically-aligned-top" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;flex-basis:50%"><!-- wp:cp-groups/group-featured-image {"aspectRatio":"16/9","width":"100%","style":{"layout":{"selfStretch":"fit","flexSize":null},"spacing":{"margin":{"bottom":"0px"}},"color":{}}} /--></div>
	<!-- /wp:column --></div>
	<!-- /wp:columns -->
	<!-- /wp:cp-groups/group-template --></div>
	<!-- /wp:cp-groups/query -->',
);
