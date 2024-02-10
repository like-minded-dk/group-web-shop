<?php

function duplicate_image($attachment_id) {
    $attachment_post = get_post($attachment_id);
    $file = get_attached_file($attachment_id);
    $new_attachment_id = wp_insert_attachment([
        'guid' => $attachment_post->guid,
        'post_mime_type' => $attachment_post->post_mime_type,
        'post_title' => $attachment_post->post_title,
        'post_content' => '',
        'post_status' => 'inherit'
    ], $file);
    // You need to require these files to use wp_generate_attachment_metadata() and wp_update_attachment_metadata()
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($new_attachment_id, $file);
    wp_update_attachment_metadata($new_attachment_id, $attach_data);

    return $new_attachment_id;
}
