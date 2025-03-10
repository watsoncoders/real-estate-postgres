<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Basic REST API for properties
 */

add_action('rest_api_init', function() {
    register_rest_route('crep/v1', '/properties', [
        'methods'  => 'GET',
        'callback' => 'crep_rest_get_properties',
    ]);
    register_rest_route('crep/v1', '/properties/(?P<id>\d+)', [
        'methods'  => 'GET',
        'callback' => 'crep_rest_get_property',
    ]);
});

function crep_rest_get_properties($request) {
    $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 10;
    $db = crep_pg_connection();
    try {
        $stmt = $db->prepare("SELECT * FROM properties ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $props = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $props;
    } catch (PDOException $e) {
        return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
    }
}

function crep_rest_get_property($request) {
    $id = intval($request->get_param('id'));
    $db = crep_pg_connection();
    try {
        $stmt = $db->prepare("SELECT * FROM properties WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $prop = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prop) {
            return new WP_Error('not_found', 'Property not found', ['status' => 404]);
        }
        return $prop;
    } catch (PDOException $e) {
        return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
    }
}
