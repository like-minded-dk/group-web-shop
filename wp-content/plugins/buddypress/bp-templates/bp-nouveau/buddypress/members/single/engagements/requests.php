<?php
/**
 * BuddyPress - Members Engagements Requests
 *
 * @since 3.0.0
 * @version 5.0.0
 */
?>

<h2 class="screen-heading engagementship-requests-screen"><?php esc_html_e( 'Engagement Requests', 'buddypress' ); ?></h2>

<?php bp_nouveau_member_hook( 'before', 'engagement_requests_content' ); ?>

<div data-bp-list="engagementship_requests">
	<?php bp_get_template_part( 'members/single/engagements/requests-loop' ); ?>
</div>

<?php bp_nouveau_member_hook( 'after', 'engagement_requests_content' );
