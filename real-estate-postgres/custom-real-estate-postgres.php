<?php
/**
 * Plugin Name: Custom Real Estate PostgreSQL
 * Plugin URI:  https://pablo-guides.com
 * Description: A WordPress plugin that stores real estate listings in PostgreSQL, uses Redis caching, offers advanced search with tsvector, geospatial queries with PostGIS, AWS S3 image uploads, WP All Import integration, user roles, front-end agent UI, SEO-friendly URLs, and a dedicated property_owners table.
 * Version:     2.1.0
 * Author:      pablo rotem
 * Author URI:  https://pablo-guides.com
 * License:     GPLv2 or later
 * Text Domain: custom-real-estate-postgres
 */

if (!defined('ABSPATH')) {
    exit;
}

// ------------------------------------------------------------------
// 1. DEFINE SETTINGS FOR POSTGRES, REDIS, AWS S3
// ------------------------------------------------------------------
define('CREP_DB_HOST', 'your_postgres_host');
define('CREP_DB_NAME', 'your_postgres_dbname');
define('CREP_DB_USER', 'your_postgres_user');
define('CREP_DB_PASS', 'your_postgres_password');
define('CREP_DB_PORT', '5432'); // Adjust if needed
define('CREP_REDIS_HOST', '127.0.0.1');
define('CREP_REDIS_PORT', 6379);

// AWS S3 (or any CDN) configuration (for image-uploads.php)
define('CREP_S3_BUCKET', 'your-bucket-name');
define('CREP_S3_REGION', 'us-east-1');
define('CREP_S3_KEY', 'AWS_ACCESS_KEY_ID');
define('CREP_S3_SECRET', 'AWS_SECRET_ACCESS_KEY');

// ------------------------------------------------------------------
// 2. INCLUDE ALL FILES
// ------------------------------------------------------------------
require_once plugin_dir_path(__FILE__) . 'includes/db-setup.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-pages.php';
require_once plugin_dir_path(__FILE__) . 'includes/frontend-templates.php';
require_once plugin_dir_path(__FILE__) . 'includes/search-redis.php';
require_once plugin_dir_path(__FILE__) . 'includes/geolocation.php';
require_once plugin_dir_path(__FILE__) . 'includes/seo-urls.php';
require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/advanced-search.php';
require_once plugin_dir_path(__FILE__) . 'includes/image-uploads.php';
require_once plugin_dir_path(__FILE__) . 'includes/roles-wpallimport.php';

// ------------------------------------------------------------------
// 3. ACTIVATE THE PLUGIN (CREATE TABLES, ROLES, TSVECTOR COLUMN, ETC.)
// ------------------------------------------------------------------
register_activation_hook(__FILE__, 'crep_plugin_activate');
function crep_plugin_activate() {
    crep_db_setup();
    crep_create_roles();
    crep_add_tsvector_column();
}

// Additional hooks if needed
