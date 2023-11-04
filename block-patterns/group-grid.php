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
	'content'    => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"inherit":true}} -->
<div class="wp-block-group" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cp-groups/query {"queryId":1,"query":{"perPage":"12","pages":0,"offset":0,"postType":"cp_group","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"cp_group_type":[]},"parents":[]},"displayLayout":{"type":"flex","columns":2},"layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"0rem","margin":{"right":"0","left":"0"},"padding":{"right":"0","left":"0"}}}} -->
<div class="wp-block-cp-groups-query" style="margin-right:0;margin-left:0;padding-right:0;padding-left:0"><!-- wp:cp-groups/group-template {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":"0"}},"layout":{"type":"constrained"}} -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60","right":"var:preset|spacing|60"},"margin":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|40"},"color":{"background":"#f3f4f6"},"dimensions":{"minHeight":"100%"}},"layout":{"inherit":true}} -->
<div class="wp-block-group has-background" style="background-color:#f3f4f6;min-height:100%;margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)"><!-- wp:cp-groups/group-featured-image {"aspectRatio":"16/9"} /-->

<!-- wp:cp-groups/group-title {"isLink":true,"style":{"spacing":{"padding":{"top":"0px","bottom":"0px","left":"0px","right":"0px"}},"typography":{"fontStyle":"normal","fontWeight":"600"}},"fontSize":"medium"} /-->

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"},"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--40);padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cp-groups/group-time-desc {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"fontSize":"small"} /-->

<!-- wp:cp-groups/group-location {"fontSize":"small"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"1rem","padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"stretch"}} -->
<div class="wp-block-group" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cp-groups/group-badge {"style":{"color":{"text":"#ae7f00"}},"fontSize":"small"} /-->

<!-- wp:cp-groups/group-badge {"badgeType":"handicap_accessible","style":{"color":{"text":"#008f5a"}},"fontSize":"small"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:cp-groups/group-template --></div>
<!-- /wp:cp-groups/query --></div>
<!-- /wp:group -->',
);
