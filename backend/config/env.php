<?php
/**
 * Lightweight .env parser — no Composer or external libraries needed.
 * Reads key=value pairs from a .env file and sets $_ENV + putenv().
 * Composer can be implemented in the future for the website
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }//if there is no path stops the function

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // Skip lines without an equals sign
        if (strpos($line, '=') === false) {
            continue;
        }

        // Split on first = only (values may contain =)
        list($key, $value) = explode('=', $line, 2);//takes the values and maps to $key and $value which is the value we want eg username, hostname, password and etc

        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");//removes the double lines, quotes, new lines and etc form the values

        $_ENV[$key] = $value; //assign the $key to $value in a global variable $_ENV 
        putenv("$key=$value");
    }
}
