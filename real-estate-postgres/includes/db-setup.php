<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Creates the PostgreSQL tables needed for the plugin and sets up connection.
 */

function crep_pg_connection() {
    static $conn = null;
    if ($conn === null) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            CREP_DB_HOST,
            CREP_DB_PORT,
            CREP_DB_NAME
        );
        try {
            $conn = new PDO($dsn, CREP_DB_USER, CREP_DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            error_log("PostgreSQL Connection Error: " . $e->getMessage());
            wp_die("Could not connect to PostgreSQL database.");
        }
    }
    return $conn;
}

function crep_db_setup() {
    /**
     * We now create a `property_owners` table to store details about the owner
     * (name, email, phone, etc.). Then the `properties.owner_id` references property_owners.id
     * instead of referencing a WordPress user ID.
     */
    $sql = [
"CREATE TABLE IF NOT EXISTS property_owners (
    id SERIAL PRIMARY KEY,
    owner_name VARCHAR(255) NOT NULL,
    owner_email VARCHAR(255) NOT NULL,
    owner_phone VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);",
"CREATE TABLE IF NOT EXISTS regions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);",
"CREATE TABLE IF NOT EXISTS cities (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    region_id INT REFERENCES regions(id) ON DELETE CASCADE
);",
"CREATE TABLE IF NOT EXISTS neighborhoods (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    city_id INT REFERENCES cities(id) ON DELETE CASCADE
);",
"CREATE TABLE IF NOT EXISTS properties (
    id SERIAL PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    price INT NOT NULL,
    rooms DECIMAL(3,1) NOT NULL,
    size INT NOT NULL,
    floor VARCHAR(50),
    property_type VARCHAR(50),
    address VARCHAR(255) NOT NULL,
    postal_code VARCHAR(10),
    region_id INT REFERENCES regions(id) ON DELETE CASCADE,
    city_id INT REFERENCES cities(id) ON DELETE CASCADE,
    neighborhood_id INT REFERENCES neighborhoods(id) ON DELETE CASCADE,
    lat DOUBLE PRECISION,
    lng DOUBLE PRECISION,
    year_built INT,
    property_condition TEXT,
    parking_spaces TEXT,
    bathrooms INT,
    balconies INT,
    listing_date DATE,
    availability_date DATE,
    property_status TEXT,
    heating TEXT,
    air_conditioning TEXT,
    pool TEXT,
    garden TEXT,
    has_elevator TEXT,
    additional_features TEXT,
    owner_id INT REFERENCES property_owners(id) ON DELETE CASCADE,
    phone TEXT NOT NULL, -- can store property phone or owner's phone
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);"
    ];

    $db = crep_pg_connection();
    foreach ($sql as $query) {
        try {
            $db->exec($query);
        } catch (PDOException $e) {
            error_log("DB Setup Error: " . $e->getMessage());
        }
    }
}
