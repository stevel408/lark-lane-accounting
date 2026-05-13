<?php
/**
 * FrontAccounting CLI Transaction Importer
 * Usage: php docker/import.php <file.json>
 */

$path_to_root = __DIR__ . "/..";

// We need a dummy remote address for session management
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Setup basic constants
if (!defined('TB_PREF')) define('TB_PREF', '0_');

// Initialize FA environment
$install_dir = is_dir($path_to_root . "/install") ? "install" : "install_bak";
require_once($path_to_root . "/$install_dir/isession.inc");

// Load configuration
if (!file_exists($path_to_root . "/config.php") || !file_exists($path_to_root . "/config_db.php")) {
    die("Error: Configuration files missing. Please run setup first.\n");
}
require_once($path_to_root . "/config.php");
require_once($path_to_root . "/config_db.php");

// Load database and core files
require_once($path_to_root . "/includes/db/connect_db.inc");
require_once($path_to_root . "/includes/ui/items_cart.inc");
require_once($path_to_root . "/gl/includes/db/gl_journal.inc");
require_once($path_to_root . "/gl/includes/gl_db.inc");
require_once($path_to_root . "/includes/access_levels.inc");
require_once($path_to_root . "/includes/main.inc");
require_once($path_to_root . "/includes/packages.inc");

// Define missing variables for access_levels.inc
global $installed_extensions, $security_areas, $security_sections;
if (!isset($installed_extensions)) $installed_extensions = array();

// Mock user session
if (!isset($_SESSION["wa_current_user"])) {
    $_SESSION["wa_current_user"] = new current_user();
    $_SESSION["wa_current_user"]->company = 0;
    $_SESSION["wa_current_user"]->user = 1; // admin
    $_SESSION["wa_current_user"]->name = 'Administrator';
    $_SESSION["wa_current_user"]->username = 'admin';
    $_SESSION["wa_current_user"]->access = 2; // Full access
}

// Establish database connection
set_global_connection();

// Check arguments
if ($argc < 2) {
    die("Usage: php docker/import.php <file.json>\n");
}

$file = $argv[1];
if (!file_exists($file)) {
    die("Error: File not found: $file\n");
}

$data = json_decode(file_get_contents($file), true);
if (!$data) {
    die("Error: Invalid JSON format.\n");
}

echo "Starting import of " . count($data) . " transactions...\n";

// Buffer output to avoid HTML footer from isession.inc
ob_start();

foreach ($data as $index => $tx) {
    // We'll write to stderr for logging so it doesn't get buffered
    fwrite(STDERR, "Processing transaction #$index: " . ($tx['memo'] ?? 'No memo') . "...\n");
    
    // Create a new Journal Cart
    $cart = new items_cart(ST_JOURNAL);
    $cart->tran_date = $tx['date'] ?? Today();
    $cart->event_date = $tx['date'] ?? Today(); // Fix for MariaDB empty date error
    $cart->doc_date = $tx['date'] ?? Today();   // Fix for MariaDB empty date error
    $cart->reference = $tx['reference'] ?? "";
    $cart->memo_ = $tx['memo'] ?? "";
    $cart->currency = $tx['currency'] ?? get_company_pref('curr_default');
    
    // Add GL items
    foreach ($tx['lines'] as $line) {
        $cart->add_gl_item(
            $line['account'],
            $line['dimension_id'] ?? 0,
            $line['dimension2_id'] ?? 0,
            $line['amount'],
            $line['memo'] ?? "",
            null, // act_descr
            $line['person_id'] ?? null,
            $line['date'] ?? null
        );
    }
    
    // Validate balance
    $balance = $cart->gl_items_total();
    if (abs($balance) > 0.001) {
        fwrite(STDERR, "Error: Transaction #$index is not balanced (Difference: $balance). Skipping.\n");
        continue;
    }
    
    // Write to database
    try {
        global $Refs;
        if (!isset($Refs)) {
            require_once($path_to_root . "/includes/references.inc");
            $Refs = new references();
        }
        
        $trans_no = write_journal_entries($cart);
        fwrite(STDERR, "Success: Journal Entry #$trans_no created.\n");
    } catch (Exception $e) {
        fwrite(STDERR, "Error: Failed to save transaction #$index: " . $e->getMessage() . "\n");
    }
}

// Clear the buffer (HTML footer)
ob_end_clean();

echo "Import complete!\n";
?>
