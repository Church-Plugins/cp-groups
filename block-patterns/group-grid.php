<?php
/**
 * Group grid block pattern
 *
 * @package CP_Groups
 */

return array(
	'title'      => __( 'Group Grid', 'cp-groups' ),
	'blockTypes' => array( 'cp-groups/query' ),
	'categories' => array( 'posts' ),
	'content'    => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cp-groups/query {"queryId":1,"query":{"perPage":"12","pages":0,"offset":0,"postType":"cp_group","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"cp_group_type":[]},"parents":[]},"displayLayout":{"type":"flex","columns":2},"layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"0rem","margin":{"right":"0","left":"0"},"padding":{"right":"0","left":"0"}}}} -->
	<div class="wp-block-cp-groups-query" style="margin-right:0;margin-left:0;padding-right:0;padding-left:0"><!-- wp:cp-groups/group-template {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":"0"}},"layout":{"type":"constrained"}} -->
	<!-- wp:group {"style":{"spacing":{"padding":{"top":"32px","bottom":"32px","left":"32px","right":"32px"},"margin":{"top":"0","bottom":"0"},"blockGap":"16px"},"color":{"background":"#f3f4f6"},"dimensions":{"minHeight":"100%"}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group has-background" style="background-color:#f3f4f6;min-height:100%;margin-top:0;margin-bottom:0;padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px"><!-- wp:cp-groups/group-featured-image {"aspectRatio":"16/9"} /-->
	
	<!-- wp:cp-groups/group-title {"isLink":true,"style":{"spacing":{"padding":{"top":"0px","bottom":"0px","left":"0px","right":"0px"}},"elements":{"link":{"color":{"text":"#1e293b"}}},"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"600"}}} /-->
	
	<!-- wp:group {"style":{"spacing":{"blockGap":"16px","margin":{"bottom":"16px"}}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
	<div class="wp-block-group" style="margin-bottom:16px"><!-- wp:cp-groups/group-time-desc {"style":{"typography":{"fontSize":"14px"}}} /-->
	
	<!-- wp:cp-groups/group-location {"style":{"typography":{"fontSize":"14px"}}} /--></div>
	<!-- /wp:group -->
	
	<!-- wp:group {"style":{"spacing":{"blockGap":"16px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"stretch"}} -->
	<div class="wp-block-group"><!-- wp:cp-groups/group-badge {"style":{"color":{"text":"#ae7f00"},"typography":{"fontSize":"14px"}}} /-->
	
	<!-- wp:cp-groups/group-badge {"badgeType":"handicap_accessible","style":{"color":{"text":"#008f5a"},"typography":{"fontSize":"14px"}}} /--></div>
	<!-- /wp:group --></div>
	<!-- /wp:group -->
	<!-- /wp:cp-groups/group-template --></div>
	<!-- /wp:cp-groups/query --></div>
	<!-- /wp:group -->',
);
