<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1) Create user roles: 'agent' + default 'subscriber'.
 * 2) WP All Import Hook: Insert data into properties + property_owners.
 */

// CREATE ROLES
function crep_create_roles() {
    add_role('agent', 'Agent', [
        'read' => true,
        'edit_posts' => false,
        'crep_manage_properties' => true,
    ]);
}

// WP ALL IMPORT HOOK
add_action('pmxi_saved_post', 'crep_wpallimport_insert_property', 10, 1);
function crep_wpallimport_insert_property($post_id) {
    // Check if it's a "property" post
    $post_type = get_post_type($post_id);
    if ($post_type !== 'property') {
        return; // not a property
    }

    $title = get_the_title($post_id);
    $price = get_post_meta($post_id, 'price', true);
    $owner_name  = get_post_meta($post_id, 'owner_name', true);
    $owner_email = get_post_meta($post_id, 'owner_email', true);
    $owner_phone = get_post_meta($post_id, 'owner_phone', true);

    $db = crep_pg_connection();
    try {
        // Insert or find existing owner. We'll always insert for demonstration:
        $stmt_owner = $db->prepare("INSERT INTO property_owners (owner_name, owner_email, owner_phone, created_at)
                                    VALUES (:n, :e, :ph, NOW()) RETURNING id");
        $stmt_owner->execute([
            'n'  => $owner_name,
            'e'  => $owner_email,
            'ph' => $owner_phone
        ]);
        $owner_id = $stmt_owner->fetchColumn();

        // Insert property
        $stmt_prop = $db->prepare("INSERT INTO properties (title, price, owner_id, phone, created_at, updated_at)
                                   VALUES (:t, :p, :o, :ph, NOW(), NOW())");
        $stmt_prop->execute([
            't'  => $title,
            'p'  => (int)$price,
            'o'  => $owner_id,
            'ph' => $owner_phone
        ]);
    } catch (PDOException $e) {
        error_log("WP All Import PG Insert Error: " . $e->getMessage());
    }
}
