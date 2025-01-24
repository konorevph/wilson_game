<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

loadEnv( ROOTPATH . '/.env');

$apiPrefix = getenv("BASE_PATH");
define('_URI', substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen($apiPrefix)));

ini_set('display_errors', 1);
error_reporting(E_ALL);

function loadEnv($file): void
{
	if (!file_exists($file)) {
		throw new Exception("Файл .env не найден.");
	}

	$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		// Пропускаем комментарии
		if (strpos($line, '#') === 0) {
			continue;
		}
		
		list($key, $value) = explode('=', $line, 2);
		
		$key = trim($key);
		$value = trim($value);
		putenv("$key=$value");
	}
}
