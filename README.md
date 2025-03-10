# Custom Real Estate PostgreSQL Plugin

A WordPress plugin by **pablo rotem** that stores real estate listings in **PostgreSQL**, uses **Redis** for caching, supports **advanced search** (including PostgreSQL `tsvector` for full-text search), **geospatial** queries with **PostGIS**, **AWS S3** (or another CDN) for image uploads, **WP All Import** integration, and a dedicated **property_owners** table. It also provides user roles (e.g., "agent") and front-end shortcodes/UI for managing properties, including a comprehensive form for all property fields.

---

## Overview

This plugin creates and manages real estate listings **outside** of the typical WordPress `wp_posts` table. Instead, it:
- Uses **PostgreSQL** to store and query properties (`properties` table) and owners (`property_owners` table).
- Uses **Redis** to cache frequently accessed queries, speeding up searches.
- Offers **full-text search** via `tsvector` and **geospatial** searches via **PostGIS**.
- Allows **image uploads** to AWS S3 (or another CDN).
- Provides a **dedicated user role** "agent" with the capability to manage properties.
- Integrates with **WP All Import** so you can import property data from CSV/XML directly into PostgreSQL.
- Exposes a **REST API** for fetching property data (`/wp-json/crep/v1/properties`).

---

## Features

1. **PostgreSQL Storage**  
   - Tables: `regions`, `cities`, `neighborhoods`, `property_owners`, `properties`.  
   - Dedicated `property_owners` table for robust owner details.

2. **Redis Caching**  
   - Caches search results for faster repeated queries.

3. **Advanced Search**  
   - **Full-text** search with `tsvector`.  
   - **Geolocation** queries using PostGIS (`ST_DistanceSphere`).

4. **Image Uploads**  
   - Placeholder logic for uploading images to **AWS S3** or another CDN.

5. **User Roles**  
   - **Agent** role can create/edit properties in the front end.  
   - **Subscriber** has read-only access by default.

6. **SEO-Friendly URLs**  
   - Access single property pages via `/property/123/my-sample-title`.

7. **WP All Import**  
   - Hook into `pmxi_saved_post` to insert property data into PostgreSQL.

8. **REST API**  
   - `GET /wp-json/crep/v1/properties`  
   - `GET /wp-json/crep/v1/properties/<id>`

---

## Requirements

- **WordPress** 5.8+ (or higher).  
- **PHP** 7.4+ (recommended 8.0+).  
- **PostgreSQL** 12+ (with [PostGIS](https://postgis.net/) extension if you want geolocation).  
- **Redis** (optional but recommended).  
- **AWS S3** (optional for image uploads).

---

## Installation Steps

1. **Clone or Download** this repository (or copy the plugin files into a folder named `custom-real-estate-postgres`).  
2. **Place the folder** inside your WordPress `wp-content/plugins/` directory or create a ZIP from it.  
3. **If you create a ZIP**:  
   - `cd` into the directory containing `custom-real-estate-postgres`.  
   - Run `zip -r custom-real-estate-postgres.zip custom-real-estate-postgres/`.  
4. **Upload & Activate** via **WordPress Admin → Plugins → Add New → Upload Plugin** if you have a ZIP file, or activate it from the Plugins screen if you placed it directly in `wp-content/plugins/`.

---

## Configuration

### PostgreSQL Setup
1. **Install PostgreSQL** on your VPS or server:
   ```bash
   sudo apt-get update
   sudo apt-get install postgresql postgresql-contrib
2. Switch to the postgres user:
    ```bash
sudo -i -u postgres
4. Create a database and user:
 ```bash
 CREATE DATABASE my_real_estate_db;
CREATE USER my_pg_user WITH PASSWORD 'my_pg_password';
GRANT ALL PRIVILEGES ON DATABASE my_real_estate_db TO my_pg_user;
5. (Optional) Enable PostGIS:
 ```bash
\c my_real_estate_db
CREATE EXTENSION postgis;
Redis Setup
1. Install Redis:
 ```bash
sudo apt-get update
sudo apt-get install redis-server
2. Enable & start:
 ```bash
sudo systemctl enable redis-server
sudo systemctl start redis-server
3. In the plugin set:
 ```bash
define('CREP_REDIS_HOST', '127.0.0.1');
define('CREP_REDIS_PORT', 6379);




   
