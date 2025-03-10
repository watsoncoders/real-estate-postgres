
```markdown
# Custom Real Estate PostgreSQL Plugin

A WordPress plugin by **pablo rotem** that stores real estate listings in **PostgreSQL**, uses **Redis** for caching, supports **advanced search** (including PostgreSQL `tsvector` for full-text search), **geospatial** queries via **PostGIS**, **AWS S3** (or another CDN) for image uploads, **WP All Import** integration, and a dedicated **property_owners** table. It also provides user roles (e.g., "agent") and front-end shortcodes/UI for managing properties, including a comprehensive form for all property fields.

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
   - **Geolocation** queries using **PostGIS** (`ST_DistanceSphere`).

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

- **WordPress** 5.8+ (or higher)  
- **PHP** 7.4+ (recommended 8.0+)  
- **PostgreSQL** 12+ (with [PostGIS](https://postgis.net/) extension if you want geolocation)  
- **Redis** (optional but recommended)  
- **AWS S3** (optional for image uploads)

---

## Installation Steps

1. **Clone or Download** this repository (or copy the plugin files into a folder named `custom-real-estate-postgres`).
2. **Place the folder** inside your WordPress `wp-content/plugins/` directory or create a ZIP from it.
3. **If you create a ZIP**:
   ```bash
   cd /path/to/your/plugin/folder
   zip -r custom-real-estate-postgres.zip custom-real-estate-postgres/
   ```
4. **Upload & Activate** via **WordPress Admin → Plugins → Add New → Upload Plugin** if you have a ZIP file, or activate it from the Plugins screen if you placed it directly in `wp-content/plugins/`.

---

## Configuration

### PostgreSQL Setup

1. **Install PostgreSQL** on your VPS or server:
   ```bash
   sudo apt-get update
   sudo apt-get install postgresql postgresql-contrib
   ```
2. **Switch to the postgres user**:
   ```bash
   sudo -i -u postgres
   ```
3. **Create a database and user**:
   ```sql
   CREATE DATABASE my_real_estate_db;
   CREATE USER my_pg_user WITH PASSWORD 'my_pg_password';
   GRANT ALL PRIVILEGES ON DATABASE my_real_estate_db TO my_pg_user;
   ```
4. **(Optional) Enable PostGIS**:
   ```sql
   \c my_real_estate_db
   CREATE EXTENSION postgis;
   ```

### Redis Setup

1. **Install Redis**:
   ```bash
   sudo apt-get update
   sudo apt-get install redis-server
   ```
2. **Enable & start**:
   ```bash
   sudo systemctl enable redis-server
   sudo systemctl start redis-server
   ```
3. **In the plugin**, set:
   ```php
   define('CREP_REDIS_HOST', '127.0.0.1');
   define('CREP_REDIS_PORT', 6379);
   ```

### AWS S3 Setup (Optional)

1. Create an **S3 bucket** in your AWS console.
2. Get **Access Key** & **Secret Key**.
3. In the main plugin file, set:
   ```php
   define('CREP_S3_BUCKET', 'your-bucket');
   define('CREP_S3_REGION', 'us-east-1');
   define('CREP_S3_KEY', 'YOUR_AWS_KEY');
   define('CREP_S3_SECRET', 'YOUR_AWS_SECRET');
   ```
4. Adjust the **`image-uploads.php`** logic to use the official AWS SDK or a 3rd-party library for production usage.

---

## Plugin Structure

```
custom-real-estate-postgres/
├─ custom-real-estate-postgres.php
├─ includes/
│  ├─ db-setup.php
│  ├─ admin-pages.php
│  ├─ frontend-templates.php
│  ├─ search-redis.php
│  ├─ geolocation.php
│  ├─ seo-urls.php
│  ├─ rest-api.php
│  ├─ advanced-search.php
│  ├─ image-uploads.php
│  └─ roles-wpallimport.php
```

- **`custom-real-estate-postgres.php`** – Main plugin file, definitions, includes, activation hook.  
- **`includes/db-setup.php`** – PostgreSQL connection & table creation (including `property_owners`).  
- **`includes/admin-pages.php`** – Admin dashboard pages for listing properties, locations, and a DB manager.  
- **`includes/frontend-templates.php`** – Shortcodes & front-end forms (including the full property creation form).  
- **`includes/search-redis.php`** – Redis caching for repeated queries.  
- **`includes/geolocation.php`** – Geospatial queries (PostGIS).  
- **`includes/seo-urls.php`** – SEO-friendly URL rewrites for single property pages.  
- **`includes/rest-api.php`** – Basic REST endpoints for properties.  
- **`includes/advanced-search.php`** – PostgreSQL `tsvector` full-text indexing & search.  
- **`includes/image-uploads.php`** – Placeholder logic for AWS S3 uploads.  
- **`includes/roles-wpallimport.php`** – Defines the "agent" role & WP All Import integration.

---

## Shortcodes

1. **`[crep_property_list limit="20"]`**  
   Displays the latest (limit) properties in a simple list.

2. **`[crep_full_property_form]`**  
   Shows a **full front-end form** to create new properties (all fields), plus an owner sub-form.

3. **`[crep_geo_search lat="32.0853" lng="34.7818" radius="5"]`**  
   Finds properties within a radius (km) of the given lat/lng (requires PostGIS).

4. **`[crep_advanced_search]`**  
   A text field for **full-text search** (PostgreSQL `tsvector`).

5. **`[crep_search_form]`**  
   A simpler search form that uses region + max price and references Redis caching.

6. **`[crep_upload_image_form]`**  
   (Optional) Example for uploading images to S3.

7. **`[crep_agent_properties]`**  
   Lists properties belonging to the logged-in agent. (Optional example.)

---

## WP All Import Integration

- **Hook**: `pmxi_saved_post`  
- The plugin uses `crep_wpallimport_insert_property()` in **`roles-wpallimport.php`** to:
  1. Insert a new row in **`property_owners`** with data from your import file.  
  2. Insert a new row in **`properties`** referencing that owner.

**Tip**: Adjust the function if you want to match existing owners or handle more property fields automatically.

---

## Usage & Examples

1. **Add a property** (front end):  
   - Log in as "agent".  
   - Go to a page with `[crep_full_property_form]`.  
   - Fill out all fields (title, description, price, region, city, lat/lng, etc.), plus the owner’s name/email/phone.  
   - Submit → property + new owner are created in PostgreSQL.

2. **Search**:  
   - Use `[crep_search_form]` for region-based search.  
   - Use `[crep_geo_search lat="32.08" lng="34.78" radius="10"]` for geolocation-based.  
   - Use `[crep_advanced_search]` for text-based search (`tsvector`).

3. **View properties**:  
   - `[crep_property_list limit="50"]` or visit a single property at `/property/123/my-title`.

4. **Image Uploads**:  
   - `[crep_upload_image_form]` provides a sample form for uploading an image to S3.  
   - For real usage, integrate the official AWS SDK or WP Offload Media plugin.

---

## Further Improvements

- **Edit / Delete** front-end form for properties (currently only create).  
- **Security**: Add nonce checks, capabilities checks, and validation for each field.  
- **Real AWS S3 code** for secure image uploads.  
- **Better handling** of owners (detect duplicates by email, etc.).  
- **Styling**: Enqueue Bootstrap or your favorite CSS framework for a nicer UI.  
- **Production**: Use caching + indexing for high performance, especially if you expect millions of listings.

---

## License

This plugin is released under the [GPLv2 (or later)](https://www.gnu.org/licenses/gpl-2.0.html). Feel free to modify and distribute as per the GPL license terms.

---

