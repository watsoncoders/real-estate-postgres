<?php
if (!defined('ABSPATH')) {
    exit;
}

function crep_redis_set($key, $value, $expiration = 300) {
    if (!class_exists('Redis')) return false;
    static $redis = null;
    if ($redis === null) {
        $redis = new Redis();
        $connected = $redis->connect(CREP_REDIS_HOST, CREP_REDIS_PORT);
        if (!$connected) return false;
    }
    $redis->setex($key, $expiration, maybe_serialize($value));
    return true;
}

function crep_redis_get($key) {
    if (!class_exists('Redis')) return false;
    static $redis = null;
    if ($redis === null) {
        $redis = new Redis();
        $connected = $redis->connect(CREP_REDIS_HOST, CREP_REDIS_PORT);
        if (!$connected) return false;
    }
    $data = $redis->get($key);
    if ($data) {
        return maybe_unserialize($data);
    }
    return false;
}

// Example search
function crep_search_properties($region_id, $max_price) {
    $cache_key = "crep_search_{$region_id}_{$max_price}";
    $cached = crep_redis_get($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $db = crep_pg_connection();
    $stmt = $db->prepare("SELECT * FROM properties WHERE region_id = :r AND price <= :p ORDER BY price LIMIT 100");
    $stmt->execute(['r' => $region_id, 'p' => $max_price]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    crep_redis_set($cache_key, $results, 300);
    return $results;
}
