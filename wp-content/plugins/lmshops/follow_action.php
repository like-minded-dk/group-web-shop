<?php
function follow_user($follower_id, $followee_id) {
    // Add $followee_id to $follower_id's 'following' array
    $current_following = get_user_meta($follower_id, 'following', true);
    if (!is_array($current_following)) {
        $current_following = [];
    }
    if (!in_array($followee_id, $current_following)) {
        $current_following[] = $followee_id;
        update_user_meta($follower_id, 'following', $current_following);
    }
}

function unfollow_user($follower_id, $followee_id) {
    // Remove $followee_id from $follower_id's 'following' array
    $current_following = get_user_meta($follower_id, 'following', true);
    if (is_array($current_following)) {
        $current_following = array_diff($current_following, [$followee_id]);
        update_user_meta($follower_id, 'following', $current_following);
    }
}


add_action('pre_get_posts', 'filter_posts_by_following');
function filter_posts_by_following($query) {
    if (is_admin() || !$query->is_main_query() || !is_home()) {
        return;
    }

    // Assuming you're not using this in the admin and only for the main posts page
    $current_user_id = get_current_user_id();
    $following = get_user_meta($current_user_id, 'following', true);

    if (!empty($following) && is_array($following)) {
        $query->set('author__in', $following);
    } else {
        // If the user is not following anyone, show no posts or a message
        $query->set('author__in', [0]); // No posts
    }
}
