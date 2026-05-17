<?php

$path_to_root = realpath(__DIR__ . "/..");

function env_first($names, $default = null)
{
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }
    }

    return $default;
}

function database_url_parts()
{
    $url = env_first(array('DATABASE_URL', 'MYSQL_URL', 'MARIADB_URL'));
    if (!$url) {
        return array();
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return array();
    }

    return array(
        'host' => $parts['host'] ?? null,
        'port' => isset($parts['port']) ? (string) $parts['port'] : null,
        'user' => isset($parts['user']) ? urldecode($parts['user']) : null,
        'password' => isset($parts['pass']) ? urldecode($parts['pass']) : null,
        'database' => isset($parts['path']) ? ltrim($parts['path'], '/') : null,
    );
}

function db_setting($key, $env_names, $default)
{
    static $url_parts = null;

    if ($url_parts === null) {
        $url_parts = database_url_parts();
    }

    $value = env_first($env_names);
    if ($value !== null) {
        return $value;
    }

    return $url_parts[$key] ?? $default;
}

$company_name = env_first(array('FA_COMPANY_NAME'), 'Lark Lane Renovation');
$connection = array(
    'host' => db_setting('host', array('FA_DB_HOST', 'MYSQLHOST', 'MARIADB_HOST'), 'db'),
    'port' => db_setting('port', array('FA_DB_PORT', 'MYSQLPORT', 'MARIADB_PORT'), '3306'),
    'dbuser' => db_setting('user', array('FA_DB_USER', 'MYSQLUSER', 'MARIADB_USER'), 'fa_user'),
    'dbpassword' => db_setting('password', array('FA_DB_PASSWORD', 'MYSQLPASSWORD', 'MARIADB_PASSWORD'), 'fa_password'),
    'dbname' => db_setting('database', array('FA_DB_NAME', 'MYSQLDATABASE', 'MARIADB_DATABASE'), 'frontacc'),
    'collation' => env_first(array('FA_DB_COLLATION'), 'utf8_unicode_ci'),
    'tbpref' => env_first(array('FA_TABLE_PREFIX'), '0_'),
    'name' => $company_name,
);

if (!file_exists($path_to_root . "/config.php")) {
    if (!copy($path_to_root . "/config.default.php", $path_to_root . "/config.php")) {
        fwrite(STDERR, "Error: Could not create config.php\n");
        exit(1);
    }
}

$config_db = <<<'PHP'
<?php

$def_coy = 0;
$tb_pref_counter = 0;
$db_connections = array (
  0 =>
  array (
    'host' => %s,
    'port' => %s,
    'dbuser' => %s,
    'dbpassword' => %s,
    'dbname' => %s,
    'collation' => %s,
    'tbpref' => %s,
    'name' => %s,
  ),
);
PHP;

$rendered = sprintf(
    $config_db,
    var_export($connection['host'], true),
    var_export($connection['port'], true),
    var_export($connection['dbuser'], true),
    var_export($connection['dbpassword'], true),
    var_export($connection['dbname'], true),
    var_export($connection['collation'], true),
    var_export($connection['tbpref'], true),
    var_export($connection['name'], true)
);

if (file_put_contents($path_to_root . "/config_db.php", $rendered) === false) {
    fwrite(STDERR, "Error: Could not write config_db.php\n");
    exit(1);
}

if (!file_exists($path_to_root . "/installed_extensions.php")) {
    $extensions = <<<'PHP'
<?php

$next_extension_id = 1;
$installed_extensions = array (
);
PHP;
    if (file_put_contents($path_to_root . "/installed_extensions.php", $extensions) === false) {
        fwrite(STDERR, "Error: Could not write installed_extensions.php\n");
        exit(1);
    }
}

echo "Runtime config written for database {$connection['host']}:{$connection['port']}/{$connection['dbname']}\n";
