<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds a tsvector column for advanced text searching, plus a function to query it.
 */

// 1) Add tsvector column & index on plugin activation
function crep_add_tsvector_column() {
    $db = crep_pg_connection();
    try {
        // Add a searchtext column for full-text
        $db->exec("ALTER TABLE properties ADD COLUMN IF NOT EXISTS searchtext tsvector");
        // Create index
        $db->exec("CREATE INDEX IF NOT EXISTS properties_search_idx ON properties USING GIN(searchtext)");
        // Populate existing rows
        $db->exec("UPDATE properties SET searchtext = to_tsvector('simple', coalesce(title,'') || ' ' || coalesce(description,''))");
        // Set a trigger to auto-update searchtext
        $trigger = "
        CREATE OR REPLACE FUNCTION properties_searchtext_trigger() RETURNS trigger AS $$
        begin
          new.searchtext := to_tsvector('simple', coalesce(new.title,'') || ' ' || coalesce(new.description,''));
          return new;
        end
        $$ LANGUAGE plpgsql;
        CREATE TRIGGER update_searchtext BEFORE INSERT OR UPDATE
        ON properties FOR EACH ROW EXECUTE PROCEDURE properties_searchtext_trigger();
        ";
        $db->exec($trigger);
    } catch (PDOException $e) {
        error_log("Full-Text Setup Error: " . $e->getMessage());
    }
}

// 2) Function to run advanced text search
function crep_fts_search($query_string, $limit = 20) {
    $db = crep_pg_connection();
    $stmt = $db->prepare("
      SELECT *, ts_rank(searchtext, to_tsquery('simple', :q)) AS rank
      FROM properties
      WHERE searchtext @@ to_tsquery('simple', :q)
      ORDER BY rank DESC
      LIMIT :limit
    ");
    $stmt->bindValue(':q', str_replace(' ', ' & ', $query_string), PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3) Shortcode for advanced search
add_shortcode('crep_advanced_search', 'crep_advanced_search_shortcode');
function crep_advanced_search_shortcode($atts) {
    // [crep_advanced_search]
    ob_start();
    ?>
    <form method="get">
        <label>Search Text: <input type="text" name="fts_query" /></label>
        <button type="submit">Search</button>
    </form>
    <?php
    if (isset($_GET['fts_query'])) {
        $q = sanitize_text_field($_GET['fts_query']);
        $results = crep_fts_search($q, 20);
        echo "<h3>Search Results for '{$q}'</h3>";
        if ($results) {
            foreach ($results as $r) {
                $slug = sanitize_title($r['title']);
                $url = home_url("property/{$r['id']}/{$slug}");
                echo "<div><a href='".esc_url($url)."'>".esc_html($r['title'])."</a> (Rank: ".esc_html($r['rank']).")</div>";
            }
        } else {
            echo "<p>No matches found.</p>";
        }
    }
    return ob_get_clean();
}
