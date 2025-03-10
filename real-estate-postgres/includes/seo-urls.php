<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'crep_register_rewrite_rules');
function crep_register_rewrite_rules() {
    add_rewrite_rule('property/([0-9]+)/?$', 'index.php?crep_property_id=$matches[1]', 'top');
    add_rewrite_rule('property/([0-9]+)/([^/]*)/?$', 'index.php?crep_property_id=$matches[1]', 'top');
}

add_filter('query_vars', function($vars) {
    $vars[] = 'crep_property_id';
    return $vars;
});

add_action('template_redirect', 'crep_property_template_redirect_seo');
function crep_property_template_redirect_seo() {
    $property_id = get_query_var('crep_property_id');
    if ($property_id) {
        crep_render_single_property($property_id);
        exit;
    }
}

function crep_render_single_property($property_id) {
    $db = crep_pg_connection();
    try {
        $stmt = $db->prepare("SELECT * FROM properties WHERE id = :id");
        $stmt->execute(['id' => $property_id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$property) {
            status_header(404);
            echo '<h1>Property Not Found</h1>';
            return;
        }
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <title><?php echo esc_html($property['title']); ?></title>
            <?php wp_head(); ?>
        </head>
        <body <?php body_class(); ?>>
            <h1><?php echo esc_html($property['title']); ?></h1>
            <p><strong>Price:</strong> <?php echo esc_html($property['price']); ?></p>
            <p><strong>Description:</strong> <?php echo esc_html($property['description']); ?></p>
            <p><strong>Owner ID:</strong> <?php echo esc_html($property['owner_id']); ?></p>
            <p><strong>Phone:</strong> <?php echo esc_html($property['phone']); ?></p>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    } catch (PDOException $e) {
        wp_die("Error: " . esc_html($e->getMessage()));
    }
}
