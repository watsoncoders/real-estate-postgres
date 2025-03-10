<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Example of storing images in AWS S3.
 * We add a 'images' JSON column if you want to store multiple image URLs.
 *
 * NOTE: For production, consider using an official AWS SDK or WP Offload Media plugin.
 */

// OPTIONAL: If you want to add an 'images' column in the DB for storing S3 URLs:
add_action('admin_init', 'crep_add_images_column');
function crep_add_images_column() {
    // This is optional, only if you want a separate column for images
    $db = crep_pg_connection();
    try {
        $db->exec("ALTER TABLE properties ADD COLUMN IF NOT EXISTS images JSON");
    } catch (PDOException $e) {
        error_log("Error adding images column: " . $e->getMessage());
    }
}

// SIMPLE S3 UPLOAD EXAMPLE (use official AWS SDK in real usage)
function crep_upload_image_to_s3($file_path, $file_name) {
    // For real usage, require the AWS SDK:
    // require 'vendor/autoload.php';
    // use Aws\S3\S3Client;
    // Then create a client:
    // $s3 = new S3Client([...]);
    // $s3->putObject([...]);

    // This is just a placeholder. 
    // In a real plugin, you'd do the actual AWS SDK upload here.
    // We'll pretend the file is uploaded and return an S3 URL:
    $url = 'https://'.CREP_S3_BUCKET.'.s3.'.CREP_S3_REGION.'.amazonaws.com/'.$file_name;
    return $url;
}

// Example front-end form for uploading an image
add_shortcode('crep_upload_image_form', 'crep_upload_image_form_shortcode');
function crep_upload_image_form_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to upload images.</p>';
    }
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <label>Select Image: <input type="file" name="crep_image"></label>
        <button type="submit" name="crep_upload_image">Upload</button>
    </form>
    <?php
    if (isset($_POST['crep_upload_image']) && !empty($_FILES['crep_image']['tmp_name'])) {
        $tmp = $_FILES['crep_image']['tmp_name'];
        $name = basename($_FILES['crep_image']['name']);
        // In real usage, you'd sanitize/validate the file
        $s3_url = crep_upload_image_to_s3($tmp, $name);
        echo "<p>Image uploaded to S3: <a href='".esc_url($s3_url)."' target='_blank'>View</a></p>";
    }
    return ob_get_clean();
}
