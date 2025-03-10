<?php
if (!defined('ABSPATH')) {
    exit;
}

function crep_geolocation_search($lat, $lng, $radius_km = 10) {
    $db = crep_pg_connection();
    $query = "
    SELECT *, ST_DistanceSphere(
        ST_MakePoint(lng, lat),
        ST_MakePoint(:user_lng, :user_lat)
    ) as distance
    FROM properties
    WHERE ST_DistanceSphere(
        ST_MakePoint(lng, lat),
        ST_MakePoint(:user_lng, :user_lat)
    ) < (:radius * 1000)
    ORDER BY distance ASC
    LIMIT 100;
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'user_lng' => $lng,
        'user_lat' => $lat,
        'radius'   => $radius_km
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

add_shortcode('crep_geo_search', 'crep_geo_search_shortcode');
function crep_geo_search_shortcode($atts) {
    $lat = isset($atts['lat']) ? floatval($atts['lat']) : 32.0853;
    $lng = isset($atts['lng']) ? floatval($atts['lng']) : 34.7818;
    $radius = isset($atts['radius']) ? floatval($atts['radius']) : 5;

    $results = crep_geolocation_search($lat, $lng, $radius);
    ob_start();
    echo "<h3>Properties within {$radius} km of [{$lat}, {$lng}]</h3>";
    if ($results) {
        echo '<ul>';
        foreach ($results as $r) {
            $slug = sanitize_title($r['title']);
            $url = home_url("property/{$r['id']}/{$slug}");
            echo '<li><a href="'.esc_url($url).'">'.esc_html($r['title']).'</a> - '.round($r['distance']).'m away</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No properties found in this radius.</p>';
    }
    return ob_get_clean();
}
