<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin pages to manage properties, locations, DB manager, etc.
 */

add_action('admin_menu', 'crep_admin_menu');
function crep_admin_menu() {
    add_menu_page(
        'Real Estate (PostgreSQL)',
        'RE PostgreSQL',
        'manage_options',
        'crep_main_menu',
        'crep_main_menu_page',
        'dashicons-admin-home',
        25
    );

    add_submenu_page(
        'crep_main_menu',
        'Manage Properties',
        'Properties',
        'manage_options',
        'crep_properties',
        'crep_properties_page'
    );

    add_submenu_page(
        'crep_main_menu',
        'Manage Locations',
        'Locations',
        'manage_options',
        'crep_locations',
        'crep_locations_page'
    );

    add_submenu_page(
        'crep_main_menu',
        'DB Manager',
        'DB Manager',
        'manage_options',
        'crep_db_manager',
        'crep_db_manager_page'
    );
}

function crep_main_menu_page() {
    echo '<div class="wrap"><h1>Real Estate PostgreSQL</h1>';
    echo '<p>Welcome to the Real Estate plugin powered by PostgreSQL.</p>';
    echo '</div>';
}

function crep_properties_page() {
    echo '<div class="wrap"><h1>Manage Properties</h1>';
    $db = crep_pg_connection();
    try {
        $stmt = $db->query("SELECT * FROM properties ORDER BY created_at DESC LIMIT 50");
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Title</th><th>Price</th><th>Owner</th><th>Created</th></tr></thead><tbody>';
        foreach ($properties as $prop) {
            echo '<tr>';
            echo '<td>'.esc_html($prop['id']).'</td>';
            echo '<td>'.esc_html($prop['title']).'</td>';
            echo '<td>'.esc_html($prop['price']).'</td>';
            echo '<td>'.esc_html($prop['owner_id']).'</td>';
            echo '<td>'.esc_html($prop['created_at']).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

    } catch (PDOException $e) {
        echo '<p style="color:red;">Error: '.esc_html($e->getMessage()).'</p>';
    }
    echo '</div>';
}

function crep_locations_page() {
    echo '<div class="wrap"><h1>Manage Locations</h1>';
    $db = crep_pg_connection();
    try {
        $regions = $db->query("SELECT * FROM regions ORDER BY name LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        echo '<h2>Regions</h2><ul>';
        foreach ($regions as $r) {
            echo '<li>'.esc_html($r['id']).' - '.esc_html($r['name']).'</li>';
        }
        echo '</ul>';

        // Similarly, display cities, neighborhoods...
    } catch (PDOException $e) {
        echo '<p style="color:red;">Error: '.esc_html($e->getMessage()).'</p>';
    }
    echo '</div>';
}

function crep_db_manager_page() {
    echo '<div class="wrap"><h1>PostgreSQL DB Manager</h1>';
    if (isset($_POST['crep_run_query'])) {
        $query = stripslashes($_POST['crep_sql_query']);
        $db = crep_pg_connection();
        try {
            $stmt = $db->query($query);
            if ($stmt) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo '<pre>'.print_r($results, true).'</pre>';
            } else {
                echo '<p>Query executed successfully (no results).</p>';
            }
        } catch (PDOException $e) {
            echo '<p style="color:red; font-weight: bold;">Error: '.$e->getMessage().'</p>';
        }
    }
    ?>
    <form method="post" style="margin-top:20px;">
        <textarea name="crep_sql_query" rows="5" cols="80"></textarea><br>
        <button type="submit" name="crep_run_query" class="button button-primary">Run Query</button>
    </form>
    <?php
    echo '</div>';
}
