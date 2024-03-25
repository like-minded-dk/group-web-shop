<?php
function replace_menu_item_with_dynamic_link($items, $args) {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $username = $current_user->user_nicename;
        $new_notifications_url = "/members/{$username}/notifications";
        $new_messages_url = "/members/{$username}/messages";
    }
    
    // Only target a specific menu by location. Change 'primary' to your menu's location.
    if ($args->theme_location == 'primary') {
        foreach ($items as $item) {
            if (is_user_logged_in()) {
                // Check for the specific menu item by title
                if ($item->title == 'Notifications') {
                    $dynamic_url = $new_notifications_url;
                } elseif ($item->title == 'Messages') {
                    $dynamic_url = $new_messages_url;
                } else {
                    $dynamic_url = $item->url;
                }
                
                $item->url = $dynamic_url;
            } else {
                unset($item);
            }
        }
    }

    return $items;
}
add_filter('wp_nav_menu_objects', 'replace_menu_item_with_dynamic_link', 10, 2);


// function add_dynamic_user_link_to_menu($items, $args) {
//     // Check if the user is logged in and the menu location matches your target location
//     error_log(json_encode($args));

//     if (is_user_logged_in() && $args->theme_location == 'primary') {
//         $current_user = wp_get_current_user();
//         $username = $current_user->user_nicename;
        
//         $slug = 'your-slug'; // Define your slug here

//         // Create the new menu item
//         https://wp.like-minded.dk/members/christopher/notifications/
//         $new_link = '<li class="menu-item"><a href="/members/' . $username . '/notifications">My notifiations</a></li>';

//         // Append new item to the menu items
//         $items .= $new_link;
//     }

//     return $items;
// }
// add_filter('wp_nav_menu_items', 'add_dynamic_user_link_to_menu', 8, 2);
