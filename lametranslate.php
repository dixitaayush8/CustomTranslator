<?php
echo <<<_END
		<html><head>PHP Form Upload</head></html><body>
		<form method="post" action="lametranslate.php" enctype="multipart/form-data">Select File of the language you're inputting: <input type="file" name="file" size="10">
		Select File of the language you wannt: <input type="file" name="fileTwo" size="10">
		Name <input type="text" name="Name">
		<input type="submit" value="upload">
		</form>
		<p><a href=index.php>Back To Home</a></p>

_END;
		echo "</body></html> \n";

		include_once 'dbconfig.php';
		$connection = new mysqli($hn, $un, $pw, $db);
		if ($connection->connect_error) 
		{
			header("dberrorpage.php");
			exit;
		}

		session_start();
		if (isset($_SESSION['username']))
		{
		    $username = $_SESSION['username'];
		    echo "</p>Hello $username!<p>";
		}