<?php
/**
 * BuddyPress - Users Friends
 *
 * @since 3.0.0
 * @version 12.0.0
 */
?>

<nav class="<?php bp_nouveau_single_item_subnav_classes(); ?>" id="subnav" role="navigation" aria-label="<?php esc_attr_e( 'Friends menu', 'buddypress' ); ?>">
	<ul id="member-secondary-nav" class="subnav bp-priority-subnav-nav-items">
		<?php if ( bp_is_my_profile() ) : ?>

			<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

		<?php endif; ?>
	</ul>

	<?php bp_nouveau_member_hook( '', 'secondary_nav' ); ?>
</nav><!-- .bp-navs -->

<?php bp_get_template_part( 'common/search-and-filters-bar' ); ?>

<?php
switch ( bp_current_action() ) :

	// Home/My Friends
	case 'my-engagements':
		bp_nouveau_member_hook( 'before', 'engagements_content' );
		?>

		<div class="members engagements" data-bp-list="members">

			<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-engagements-loading' ); ?></div>

		</div><!-- .members.engagements -->

		<?php
		bp_nouveau_member_hook( 'after', 'engagements_content' );
		break;

	case 'requests':
		bp_get_template_part( 'members/single/engagements/requests' );
		break;

	// Any other
	default:
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
