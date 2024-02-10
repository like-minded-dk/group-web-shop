<?php
function set_product_images(WC_Product_Simple $product) {
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    $gallery_image_ids = [];

    // Check if images were uploaded
    if (isset($_FILES['offer_image']) && is_array($_FILES['offer_image']['name'])) {
        $file_count = count($_FILES['offer_image']['name']);

        for ($i = 0; $i < $file_count; $i++) {
            // Skip processing if the file name is empty (no file uploaded for this index)
            if (empty($_FILES['offer_image']['name'][$i])) continue;

            // Construct a file array for the current file
            $file = [
                'name'     => $_FILES['offer_image']['name'][$i],
                'type'     => $_FILES['offer_image']['type'][$i],
                'tmp_name' => $_FILES['offer_image']['tmp_name'][$i],
                'error'    => $_FILES['offer_image']['error'][$i],
                'size'     => $_FILES['offer_image']['size'][$i]
            ];

            // Temporarily store the file information in a variable to pass to media_handle_upload
            $_FILES['upload_attachment'] = $file;

            // Attempt to upload the file and get the attachment ID
            $attachment_id = media_handle_upload('upload_attachment', 0); // 0 denotes no parent post ID

            if (is_wp_error($attachment_id)) {
                wp_die('An error occurred uploading an image: ' . $attachment_id->get_error_message());
            } else {
                $gallery_image_ids[] = $attachment_id;
            }

            // Clean up, remove the temporary file array
            unset($_FILES['upload_attachment']);
        }
    }

    // Set the first image as the product's main image and the rest as gallery images
    if (!empty($gallery_image_ids)) {
        $product->set_image_id(array_shift($gallery_image_ids)); // Set the first image as main image
        $product->set_gallery_image_ids($gallery_image_ids); // Set the rest as gallery images
        $product->save();
    }
}
