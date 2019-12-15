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
				else if (field.length < 5)
				{
					return "Username must be at least 5 characters. "
				}
				else if (!(/[a-zA-Z0-9_-]/).test(field))
				{
					return "Only a-z, A-Z, 0-9, - and _ allowed in usernames. "
				}
				return ""
			}

			function validatePassword(field)
			{
				if (field == "")
				{
					return "No password was entered."
				}
				else if (field.length < 6)
				{
					return "Password must be at least 6 characters"
				}
				else if ( !(/[a-z]/).test(field) || !(/[A-Z]/).test(field) || !(/[0-9]/).test(field) )
				{
					return "Password must require one each of a-z, A-Z, and 0-9."
				}
				return ""
			}
		</script>
		</head>

		<body>
		<p>Sign Up</p>

		<form method="post" action="signup.php" onSubmit="return validate(this)"><pre>
		Username <input type="text" name="username">
		Password <input type="text" name="password">
		<input type="submit" value="Sign Up">
		</pre></form>
		<p><a href=index.php>Back To Home</a></p>
		</body>
		</html>

_END;

		function execute()
		{
			include_once 'dbconfig.php';
			$connection = new mysqli($hn, $un, $pw, $db);
			if ($connection->connect_error) 
			{
				header("dberrorpage.php");
				exit;
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

			if(isset($_POST['username']) && isset($_POST['password']) && validate_username($username) && validate_password($password))
			{
					$salt = microtime();
					$token = hash('ripemd128', $salt . $password);
					$query = "INSERT INTO Users VALUES('$username', '$salt', '$token');";
					$result = $connection->query($query);
					if (!$result)
					{
						$connection->close();
						header("Location:dberrorpage.php");
						exit;
					}
					else
					{
						$_SESSION['username'] = $username;
						$_SESSION['password'] = $password;
						$connection->close();
						die ("<p><a href=lametranslate.php>Click here to continue</a></p>");
					}
			}
			$connection->close();

		}

		function validate_username($field)
		{
			if ($field == "")
			{
				return false;
			}
			else if (strlen($field) < 5)
			{
				return false;
			}
			else if (preg_match("/[^a-zA-Z0-9_-]/", $field))
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
			else if (strlen($field) < 6)
			{
				return false;
			}
			else if ((!preg_match("/[a-z]/", $field)) || (!preg_match("/[A-Z]/", $field)) || (!preg_match("/[0-9]/", $field)))
			{
				return false;
			}
			return true;
		}

		

		function mysql_entities_fix_string($connection, $string) {
			return htmlentities(mysql_fix_string($connection, $string));
		}

		function mysql_fix_string($connection, $string) {
			if (get_magic_quotes_gpc()) $string = stripslashes($string);
				return $connection->real_escape_string($string);
		}
		
		execute()
?>
