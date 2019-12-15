<?php

		include_once 'dbconfig.php';
		$connection = new mysqli($hn, $un, $pw, $db);
		if ($connection->connect_error) 
		{
			header("dberrorpage.php");
			exit;
		}

		session_start();
		$_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
		if ($_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']))    
		{
			$connection->close();
			destroy_session_and_data();
			header("Location:index.php");
		    exit;
		}
		if (isset($_SESSION['username']))
		{
			echo <<<_END
		<html><head>PHP Form Upload</head><body>
		<form method="post" action="lametranslate.php" enctype="multipart/form-data">Select File of the language you're inputting: <input type="file" name="file[]" size="10">
		Select File of the language you want: <input type="file" name="file[]" size="10">
		Name <input type="text" name="Name">
		<input type="submit" value="upload">
		<input type="submit" name="logout"
                value="Logout"/> 
		</form>
		<p><a href=index.php>Back To Home</a></p>

_END;

		echo "</body></html> \n";
		    $username = $_SESSION['username'];
		    echo "</p>Hello $username! By default, you will get a default translation if you do not upload. However, feel free to upload your own translation files.<p>";
		    if (isset($_POST['logout']))
		    {
		    	$connection->close();
		    	destroy_session_and_data();
		    	header("Location:index.php");
		    	exit;
		    }
		    if (isset($_POST['Name']) && $_FILES['file']['name'][0] && $_FILES['file']['name'][1])
		    {
		    	$fileFirst = $_FILES["file"]["name"][0];
		    	$fileSecond = $_FILES["file"]["name"][1];
		    	$completeFilePathFirst = strval($_FILES["file"]["tmp_name"][0]);
		    	$completeFilePathSecond = strval($_FILES["file"]["tmp_name"][1]);
		        $extFirst = (explode(".", $fileFirst));
		        $extSecond = (explode(".", $fileSecond));
				$ext_first = end($extFirst);
				$ext_second = end($extSecond);
		        if ($ext_first != 'txt' && $ext_second != 'txt')
		        {
		            $connection->close();
					header("Location:fileioerror.php");
					exit;
		        }
		        else
		        {
		        	print_r($_FILES);
		        	$filename = stripslashes($_FILES['file']['name'][0]);
					$extension = $extFirst;
					$newfilename =$_FILES['file']['name'][0];
					$newFileDir = $_SERVER['DOCUMENT_ROOT'].'/'.basename($filename);
					copy($completeFilePathFirst,$newFileDir);
					$completeFilePathFirst = $newFileDir;

					$filenameOne = stripslashes($_FILES['file']['name'][1]);
					$extensionOne = $extSecond;
					$newfilenameOne =$_FILES['file']['name'][1];
					$newFileDirOne = $_SERVER['DOCUMENT_ROOT'].'/'.basename($filenameOne);
					copy($completeFilePathSecond,$newFileDirOne);
					$completeFilePathSecond = $newFileDirOne;
		        }
		        $text = strtolower(mysql_entities_fix_string($connection, $_POST['Name']));
				$res = $connection->query("SELECT * FROM UserTranslation WHERE username='$username';");
				if (!$res->num_rows)
				{
					$resTwo = $connection->query("INSERT INTO UserTranslation VALUES('$username', '$completeFilePathFirst', '$completeFilePathSecond');");
					if (!$resTwo)
					{
						$res->close();
						$connection->close();
						header("Location:dberrorpage.php");
						exit;
					}
				}
				else
				{
					$res->close();
					echo "hi";
					$resThree = $connection->query("UPDATE UserTranslation SET filepath='$completeFilePathFirst', filepath2='$completeFilePathSecond' WHERE username='$username';");
					if (!$resThree)
					{
						$resThree->close();
						$connection->close();
						header("Location:dberrorpage.php");
						exit;
					}
				}

				$filepathInitial = $completeFilePathFirst;
				$filepathInitialTwo = $completeFilePathSecond;
				translate($connection, $filepathInitial, $filepathInitialTwo, $text);
				$connection->close();
		}

		else if (isset($_POST['Name']))
		{
			$text = strtolower(mysql_entities_fix_string($connection, $_POST['Name']));
				$res = $connection->query("SELECT * FROM UserTranslation WHERE username='$username';");
				if (!$res->num_rows)
				{
					$res->close();
					$resTwo = $connection->query("SELECT * FROM UserTranslation WHERE username='default'");
					if (!$resTwo)
					{
						$connection->close();
						header("Location:dberrorpage.php");
						exit;
					}
					else if($resTwo->num_rows)
					{
						$row = $resTwo->fetch_array(MYSQLI_NUM);
						$resTwo->close();
						$filepathInitial = $row[1];
						$filepathInitialTwo = $row[2];
						translate($connection,$filepathInitial, $filepathInitialTwo, $text);
				}
			}
				else if ($res->num_rows)
				{
					$row = $res->fetch_array(MYSQLI_NUM);
					$res->close();
					$filepathInitial = $row[1];
					$filepathInitialTwo = $row[2];
					translate($connection, $filepathInitial, $filepathInitialTwo, $text);
				}
				$connection->close();
		}
	}
		else
		{
			echo <<<_END
		<html><head>PHP Form Upload</head><body>
		<form method="post" action="lametranslate.php" enctype="multipart/form-data">
		Name <input type="text" name="Name">
		<input type="submit" value="upload">
		</form>
		<p><a href=index.php>Back To Home</a></p>

_END;
			echo "</body></html> \n";
			if (isset($_POST['Name']))
			{

				$text = strtolower(mysql_entities_fix_string($connection, $_POST['Name']));
				$res = $connection->query("SELECT * FROM UserTranslation WHERE username='default'");
				if (!$res)
				{
					$connection->close();
					header("Location:dberrorpage.php");
					exit;
				}
				else if ($res->num_rows)
				{
					$row = $res->fetch_array(MYSQLI_NUM);
					$res->close();
					$filepathInitial = $row[1];
					$filepathInitialTwo = $row[2];
					translate($connection, $filepathInitial, $filepathInitialTwo, $text);			
				}
			}
			$connection->close();

		}

		function destroy_session_and_data() {
			$_SESSION = array();
			setcookie(session_name(), '', time() - 2592000, '/');
			session_destroy();
		}

		function mysql_entities_fix_string($connection, $string) {
			return htmlentities(mysql_fix_string($connection, $string));
		}

		function mysql_fix_string($connection, $string) {
			if (get_magic_quotes_gpc()) $string = stripslashes($string);
				return $connection->real_escape_string($string);
		}

		function translate($connection, $file1, $file2, $text)
		{
			$fileOpener = fopen($file1, 'r');
				if (!fopen($file1, 'r'))
				{
					$connection->close();
					header("Location:fileioerror.php");
					exit;
				}
	    		$fileSize = fileSize($file1);
	    		$key = -1;
	    		if(flock($fileOpener, LOCK_EX))
	    		{
	    			$line = fread($fileOpener, $fileSize);
	    			$array_of_strings = preg_split('/\s+/',$line);
	    			$lowercase_array_of_strings = array_map('strtolower', $array_of_strings);
	    			$lowercase_array_of_strings = array_map('trim', $lowercase_array_of_strings);
	    			if (is_numeric(array_search($text, $lowercase_array_of_strings)))
	    			{
	    				$key = array_search($text, $lowercase_array_of_strings);
	    				
	    			}
	    			flock($fileOpener, LOCK_UN); 
	    		}
	    		fclose($fileOpener);
	    		if ($key == -1)
	    		{
	    			echo "No translation available for this text. Please enter a different text.";
	    		}
	    		else
	    		{
		    		$filepathInitialTwo = $file2;
					$fileOpenerTwo = fopen($filepathInitialTwo, 'r');
					if (!fopen($filepathInitialTwo, 'r'))
					{
						$connection->close();
						header("Location:fileioerror.php");
						exit;
					}
		    		$fileSizeTwo = fileSize($filepathInitialTwo);
		    		$translation = "";
		    		if(flock($fileOpenerTwo, LOCK_EX))
		    		{
		    			$line = fread($fileOpenerTwo, $fileSizeTwo);
		    			$array_of_strings = preg_split('/\s+/',$line);
		    			$lowercase_array_of_strings = array_map('strtolower', $array_of_strings);
		    			$lowercase_array_of_strings = array_map('trim', $lowercase_array_of_strings);
		    			if ($key < count($lowercase_array_of_strings) && $key >= 0)
		    			{
		    				if ($lowercase_array_of_strings[$key])
		    				{
			    				$translation = $lowercase_array_of_strings[$key];
			    				echo $translation;
		    				}
		    			}
		    			else
		    			{
		    				echo "No translation available for this text. Please enter a different text.";
		    			}
		    			flock($fileOpenerTwo, LOCK_UN); 
		    		}
		    		fclose($fileOpenerTwo);
	    		}
		}
?>