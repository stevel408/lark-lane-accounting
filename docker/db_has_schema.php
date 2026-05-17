<?php

require __DIR__ . "/runtime_config.php";

$mysqli = @new mysqli(
    $connection['host'],
    $connection['dbuser'],
    $connection['dbpassword'],
    $connection['dbname'],
    (int) $connection['port']
);

if ($mysqli->connect_error) {
    fwrite(STDERR, "Error: Could not connect to database: {$mysqli->connect_error}\n");
    exit(2);
}

$prefix = $mysqli->real_escape_string($connection['tbpref']);
$result = $mysqli->query("SHOW TABLES LIKE '{$prefix}users'");

if ($result && $result->num_rows > 0) {
    echo "FrontAccounting schema already exists.\n";
    exit(0);
}

echo "FrontAccounting schema not found.\n";
exit(1);
