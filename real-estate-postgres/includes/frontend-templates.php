<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend shortcodes for listing properties, user submissions, etc.
 */

// 1) LIST LATEST PROPERTIES (unchanged)
add_shortcode('crep_property_list', 'crep_property_list_shortcode');
function crep_property_list_shortcode($atts) {
    $limit = isset($atts['limit']) ? intval($atts['limit']) : 20;
    $db = crep_pg_connection();
    try {
        $stmt = $db->prepare("SELECT p.*, o.owner_name FROM properties p
                              LEFT JOIN property_owners o ON p.owner_id = o.id
                              ORDER BY p.created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        echo '<div class="crep-property-list">';
        foreach ($properties as $p) {
            $slug = sanitize_title($p['title']);
            $url = home_url("property/{$p['id']}/{$slug}");
            ?>
            <div style="margin-bottom:20px;">
                <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($p['title']); ?></a></h3>
                <p>Price: <?php echo esc_html($p['price']); ?></p>
                <p>Owner: <?php echo esc_html($p['owner_name']); ?></p>
            </div>
            <?php
        }
        echo '</div>';
        return ob_get_clean();
    } catch (PDOException $e) {
        return '<p style="color:red;">Error: '.esc_html($e->getMessage()).'</p>';
    }
}

// 2) FULL PROPERTY FORM (Agent UI) - create / edit property with all fields
add_shortcode('crep_full_property_form', 'crep_full_property_form_shortcode');
function crep_full_property_form_shortcode($atts) {
    // [crep_full_property_form]
    // For demonstration, we let ANY logged-in user add a property. 
    // Ideally, you'd restrict to role = 'agent' or 'administrator'.
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to add properties.</p>';
    }

    $db = crep_pg_connection();

    // If form is submitted
    if (isset($_POST['crep_submit_property'])) {
        // 1) Insert into property_owners
        $owner_name  = sanitize_text_field($_POST['owner_name']);
        $owner_email = sanitize_email($_POST['owner_email']);
        $owner_phone = sanitize_text_field($_POST['owner_phone']);

        try {
            // Insert or find existing owner?
            // For simplicity, we always create a new row
            $stmt_owner = $db->prepare("INSERT INTO property_owners (owner_name, owner_email, owner_phone, created_at)
                                        VALUES (:n, :e, :ph, NOW()) RETURNING id");
            $stmt_owner->execute([
                'n'  => $owner_name,
                'e'  => $owner_email,
                'ph' => $owner_phone
            ]);
            $owner_id = $stmt_owner->fetchColumn();

            // 2) Insert into properties
            $data = [
                'title'              => sanitize_text_field($_POST['title']),
                'description'        => wp_kses_post($_POST['description']),
                'price'              => intval($_POST['price']),
                'rooms'              => floatval($_POST['rooms']),
                'size'               => intval($_POST['size']),
                'floor'              => sanitize_text_field($_POST['floor']),
                'property_type'      => sanitize_text_field($_POST['property_type']),
                'address'            => sanitize_text_field($_POST['address']),
                'postal_code'        => sanitize_text_field($_POST['postal_code']),
                'region_id'          => intval($_POST['region_id']),
                'city_id'            => intval($_POST['city_id']),
                'neighborhood_id'    => intval($_POST['neighborhood_id']),
                'lat'                => floatval($_POST['lat']),
                'lng'                => floatval($_POST['lng']),
                'year_built'         => intval($_POST['year_built']),
                'property_condition' => sanitize_text_field($_POST['property_condition']),
                'parking_spaces'     => sanitize_text_field($_POST['parking_spaces']),
                'bathrooms'          => intval($_POST['bathrooms']),
                'balconies'          => intval($_POST['balconies']),
                'listing_date'       => sanitize_text_field($_POST['listing_date']),
                'availability_date'  => sanitize_text_field($_POST['availability_date']),
                'property_status'    => sanitize_text_field($_POST['property_status']),
                'heating'            => sanitize_text_field($_POST['heating']),
                'air_conditioning'   => sanitize_text_field($_POST['air_conditioning']),
                'pool'               => sanitize_text_field($_POST['pool']),
                'garden'             => sanitize_text_field($_POST['garden']),
                'has_elevator'       => sanitize_text_field($_POST['has_elevator']),
                'additional_features'=> sanitize_text_field($_POST['additional_features']),
                'owner_id'           => $owner_id,
                'phone'              => sanitize_text_field($_POST['owner_phone'])
            ];

            $sql = "INSERT INTO properties
            (title, description, price, rooms, size, floor, property_type, address, postal_code,
             region_id, city_id, neighborhood_id, lat, lng, year_built, property_condition,
             parking_spaces, bathrooms, balconies, listing_date, availability_date, property_status,
             heating, air_conditioning, pool, garden, has_elevator, additional_features,
             owner_id, phone, created_at, updated_at)
            VALUES
            (:title, :description, :price, :rooms, :size, :floor, :property_type, :address, :postal_code,
             :region_id, :city_id, :neighborhood_id, :lat, :lng, :year_built, :property_condition,
             :parking_spaces, :bathrooms, :balconies, :listing_date, :availability_date, :property_status,
             :heating, :air_conditioning, :pool, :garden, :has_elevator, :additional_features,
             :owner_id, :phone, NOW(), NOW())";

            $stmt_prop = $db->prepare($sql);
            $stmt_prop->execute($data);

            echo '<p style="color:green;">Property created successfully!</p>';

        } catch (PDOException $e) {
            echo '<p style="color:red;">Error: '.esc_html($e->getMessage()).'</p>';
        }
    }

    // Display form
    ob_start();
    ?>
    <h3>Create New Property</h3>
    <form method="post">
        <p><label>Title: <input type="text" name="title" required></label></p>
        <p><label>Description:<br>
            <textarea name="description" rows="4" cols="50"></textarea>
        </label></p>
        <p><label>Price: <input type="number" name="price" required></label></p>
        <p><label>Rooms: <input type="number" step="0.5" name="rooms" required></label></p>
        <p><label>Size (m²): <input type="number" name="size"></label></p>
        <p><label>Floor: <input type="text" name="floor"></label></p>
        <p><label>Property Type: <input type="text" name="property_type"></label></p>
        <p><label>Address: <input type="text" name="address" required></label></p>
        <p><label>Postal Code: <input type="text" name="postal_code"></label></p>
        <p><label>Region ID: <input type="number" name="region_id"></label></p>
        <p><label>City ID: <input type="number" name="city_id"></label></p>
        <p><label>Neighborhood ID: <input type="number" name="neighborhood_id"></label></p>
        <p><label>Latitude: <input type="text" name="lat"></label></p>
        <p><label>Longitude: <input type="text" name="lng"></label></p>
        <p><label>Year Built: <input type="number" name="year_built"></label></p>
        <p><label>Condition: <input type="text" name="property_condition"></label></p>
        <p><label>Parking Spaces: <input type="text" name="parking_spaces"></label></p>
        <p><label>Bathrooms: <input type="number" name="bathrooms"></label></p>
        <p><label>Balconies: <input type="number" name="balconies"></label></p>
        <p><label>Listing Date: <input type="date" name="listing_date"></label></p>
        <p><label>Availability Date: <input type="date" name="availability_date"></label></p>
        <p><label>Status (for sale/rent...): <input type="text" name="property_status"></label></p>
        <p><label>Heating: <input type="text" name="heating"></label></p>
        <p><label>Air Conditioning: <input type="text" name="air_conditioning"></label></p>
        <p><label>Pool: <input type="text" name="pool"></label></p>
        <p><label>Garden: <input type="text" name="garden"></label></p>
        <p><label>Has Elevator: <input type="text" name="has_elevator"></label></p>
        <p><label>Additional Features: <input type="text" name="additional_features"></label></p>

        <hr>
        <h4>Owner Details</h4>
        <p><label>Owner Name: <input type="text" name="owner_name" required></label></p>
        <p><label>Owner Email: <input type="email" name="owner_email" required></label></p>
        <p><label>Owner Phone: <input type="text" name="owner_phone" required></label></p>

        <p><button type="submit" name="crep_submit_property">Create Property</button></p>
    </form>
    <?php
    return ob_get_clean();
}
