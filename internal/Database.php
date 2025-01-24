<?php

class Database
{
	public static function getConnection(): PDO
	{
		$host = getenv('DB_HOST');
		$dbname = getenv('DB_NAME');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
	
		$dsn = "pgsql:host=$host;dbname=$dbname";
		$pdo = new PDO($dsn, $user, $pass);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $pdo;
	}
}