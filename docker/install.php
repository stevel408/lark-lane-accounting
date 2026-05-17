<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use absolute path relative to this script to avoid CWD issues
$path_to_root = realpath(__DIR__ . "/..");

require_once(__DIR__ . "/runtime_config.php");

// FrontAccounting installation variables.
$db_host = $connection['host'];
$db_port = $connection['port'];
$db_user = $connection['dbuser'];
$db_pass = $connection['dbpassword'];
$db_name = $connection['dbname'];
$company_name = getenv('FA_COMPANY_NAME') ?: 'Lark Lane Renovation';
$admin_password = getenv('FA_ADMIN_PASSWORD') ?: 'password';
$sql_template = getenv('FA_SQL_TEMPLATE') ?: 'en_US-new.sql';

// Setup environment
if (!defined('TB_PREF')) define('TB_PREF', '0_');

// Include the installer session handler from our stable local support copy.
require_once($path_to_root . "/install_custom/isession.inc");

// Include additional required FA files
require_once($path_to_root . "/includes/db/connect_db.inc");
require_once($path_to_root . "/admin/db/maintenance_db.inc");
require_once($path_to_root . "/admin/db/company_db.inc");
require_once($path_to_root . "/admin/db/users_db.inc");

$connection = array(
    'host' => $db_host,
    'port' => $db_port,
    'dbuser' => $db_user,
    'dbpassword' => $db_pass,
    'dbname' => $db_name,
    'collation' => 'utf8_unicode_ci',
    'tbpref' => '0_',
    'name' => $company_name
);

// db_query() rewrites the TB_PREF placeholder using this global connection list.
// Set it before importing so FrontAccounting keeps the 0_ table prefix.
global $def_coy, $db_connections, $tb_pref_counter;
$def_coy = 0;
$tb_pref_counter = 0;
$db_connections = array(
    0 => $connection
);

echo "Connecting to database...\n";
global $db;
$db = db_create_db($connection);
if (!$db) {
    die("Error: Could not connect to or create database.\n");
}

echo "Database connected. Encoding: " . db_get_charset($db) . "\n";

$sql_file = $path_to_root . '/sql/' . $sql_template;
echo "Importing schema from $sql_file...\n";

// We'll wrap db_import to capture errors and be more verbose
function debug_db_import($filename, $connection) {
    global $db;

    // Explicitly check if file exists and is readable
    if (!is_readable($filename)) {
        echo "Error: SQL file is not readable.\n";
        return false;
    }

    $lines = file($filename);
    echo "Total lines in SQL file: " . count($lines) . "\n";

    // We'll use the original db_import but with $return_errors = true
    $result = db_import($filename, $connection, true, true, false, true);

    if (is_array($result)) {
        echo "SQL Import encountered errors:\n";
        foreach ($result as $err) {
            echo "Line {$err[1]}: {$err[0]}\n";
        }
        return false;
    } elseif ($result === true) {
        echo "SQL Import reported success.\n";
        return true;
    } else {
        echo "SQL Import failed with unknown error.\n";
        return false;
    }
}

if (!debug_db_import($sql_file, $connection)) {
    die("Error: Database import failed.\n");
}

// Verify tables
$res = db_query("SHOW TABLES LIKE '0_users'");
if (db_num_rows($res) == 0) {
    echo "CRITICAL ERROR: 0_users table was NOT created even though import reported success!\n";

    // Let's try to list all tables
    $res = db_query("SHOW TABLES");
    echo "Tables in database:\n";
    while ($row = db_fetch_row($res)) {
        echo "- " . $row[0] . "\n";
    }
    die("Aborting due to missing tables.\n");
} else {
    echo "Verified: 0_users table exists.\n";
}

echo "Configuring company and admin user...\n";
// update_company_prefs uses $_SESSION['SysPrefs']
$SysPrefs->refresh(); // Initialize with DB values

if (!update_company_prefs(array('coy_name' => $company_name))) {
    echo "Warning: Could not update company name.\n";
}

$admin = get_user_by_login('admin');
if ($admin) {
    echo "Updating admin password...\n";
    if (!update_user_prefs($admin['id'], array(
        'password' => md5($admin_password)
    ))) {
        echo "Warning: Could not update admin password.\n";
    }
} else {
    echo "Warning: Admin user not found in database.\n";
}

echo "Writing configuration files...\n";
if (!file_exists($path_to_root . "/config.php")) {
    if (!copy($path_to_root . "/config.default.php", $path_to_root . "/config.php")) {
        die("Error: Could not create config.php\n");
    }
}

$res = write_config_db(false);
if ($res != 0) {
    die("Error: Could not write config_db.php (Code: $res)\n");
}

echo "Writing extension registry...\n";
global $installed_extensions, $next_extension_id;
$installed_extensions = array();
$next_extension_id = 1;
if (!write_extensions($installed_extensions)) {
    die("Error: Could not write installed_extensions.php\n");
}

echo "Installation complete!\n";

// Flush output
while (ob_get_level() > 0) {
    ob_end_flush();
}
?>
