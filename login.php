<?php
echo <<<_END
<html><head>
		<script>
			function validate(form)
			{
				fail = validateUsername(form.username.value);
				fail += validatePassword(form.password.value);
				if (fail == "")
				{
					return true;
				}
				else
				{
					alert(fail);
					return false;
				}
			}

			function validateUsername(field)
			{
				if (field == "")
				{
					return "No username was entered. "
				}
				return ""
			}

			function validatePassword(field)
			{
				if (field == "")
				{
					return "No password was entered."
				}
				return ""
			}
		</script>
		</head>

		<body>
		<p>Sign Up</p>

		<form method="post" action="login.php" onSubmit="return validate(this)"><pre>
		Username <input type="text" name="username">
		Password <input type="text" name="password">
		<input type="submit" value="Log in">
		</pre></form>
		<p><a href=index.php>Back To Home</a></p>
		</body>
		</html>

_END;


		include_once 'dbconfig.php';
		$connection = new mysqli($hn, $un, $pw, $db);
		if ($connection->connect_error) 
		{
			header("dberrorpage.php");
			exit;
		}


		function validate_username($field)
		{
			if ($field == "")
			{
				return false;
			}
			return true;
		}

		function validate_password($field)
		{
			if ($field == "")
			{
				return false;
			}
			return true;
		}

		$username = "";
		$password = "";
		if (isset($_POST['username']))
		{
			$username = mysql_entities_fix_string($connection, $_POST['username']);
		}
		if (isset($_POST['password']))
		{
			$password = mysql_entities_fix_string($connection, $_POST['password']);
		}

		session_start();
		if (isset($_SESSION['username']))
		{
			$connection->close();
			header("Location:lametranslate.php");
			exit;
		}
		else if(isset($_POST['username']) && isset($_POST['password']) && validate_username($username) && validate_password($password))
		{
			$username = mysql_entities_fix_string($connection, $_POST['username']);
			$password = mysql_entities_fix_string($connection, $_POST['password']);
			$res = $connection->query("SELECT * FROM Users WHERE username='$username'");
			if(!$res)
			{
				$connection->close();
				header("dberrorpage.php");
				exit;
			}
			else if ($res->num_rows)
			{
				$row = $res->fetch_array(MYSQLI_NUM);
				$res->close();
				$salt = $row[1];
				$token = hash('ripemd128', $salt . $password);
				if ($token == $row[2])
				{
					session_start();
					$_SESSION['username'] = $username;
					$_SESSION['password'] = $password;
					$connection->close();
					die ("<p><a href=lametranslate.php>Click here to continue</a></p>");
				}
				else
				{
					$connection->close();
					echo "<p>Doesn't look like you used a valid password. It doesn't match with the username you provided. Please enter a valid password.</p>";
				}
				
			}
			else
			{
				$connection->close();
				echo "<p>Doesn't look like you used a valid username. It currently does not exist in the database. Please enter in a valid username.</p>";
			}
		}
	
	function mysql_entities_fix_string($connection, $string) {
		return htmlentities(mysql_fix_string($connection, $string));
	}

	function mysql_fix_string($connection, $string) {
		if (get_magic_quotes_gpc()) $string = stripslashes($string);
			return $connection->real_escape_string($string);
	}
