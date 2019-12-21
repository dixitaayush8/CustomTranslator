<?php
include_once 'dbconfig.php';
	$connection = new mysqli($hn, $un, $pw, $db);
	if ($connection->connect_error) 
	{
		die($connection->error);
	}

$create_translationtb = "CREATE TABLE UserTranslation(username VARCHAR(128) PRIMARY KEY, filepath BLOB, filepath2 BLOB);";

$create_usertb = "CREATE TABLE Users(username VARCHAR(128) PRIMARY KEY, salt VARCHAR(128) UNIQUE, hash VARCHAR(128));";


$result = $connection->query($create_usertb);

if (!$result) {
	die($connection->error);
	echo "create translation table failed";
}

$result = $connection->query($create_translationtb);

if (!$result) {
	die($connection->error);
	echo "create user table failed";
}

// create default translation template
$username = 'default';
$filepath1 = __DIR__.'/'.'filedictionary1.txt';
$filepath2 = __DIR__.'/'.'filedictionary2.txt';
$create_default_translation = "INSERT INTO UserTranslation VALUES('$username', '$filepath1', '$filepath2')";
$result = $connection->query($create_default_translation);
if (!$result) {
	die($connection->error);
	echo "create default translation failed";
}

$connection->close();
echo "db inited";


function mysql_entities_fix_string($connection, $string) {
	return htmlentities(mysql_fix_string($connection, $string));
}

function mysql_fix_string($connection, $string) {
	if (get_magic_quotes_gpc()) $string = stripslashes($string);
		return $connection->real_escape_string($string);
}

?>