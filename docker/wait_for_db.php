<?php

require __DIR__ . "/runtime_config.php";

$max_attempts = (int) (getenv('FA_DB_WAIT_ATTEMPTS') ?: 60);
$delay = (int) (getenv('FA_DB_WAIT_DELAY') ?: 2);

for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
    $mysqli = @new mysqli(
        $connection['host'],
        $connection['dbuser'],
        $connection['dbpassword'],
        $connection['dbname'],
        (int) $connection['port']
    );

    if (!$mysqli->connect_error) {
        echo "Database is reachable.\n";
        exit(0);
    }

    fwrite(STDERR, "Waiting for database ({$attempt}/{$max_attempts}): {$mysqli->connect_error}\n");
    sleep($delay);
}

fwrite(STDERR, "Error: Database was not reachable after {$max_attempts} attempts.\n");
exit(1);
