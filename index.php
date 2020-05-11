<?php

	/**
	 * MySQL tables used in the Project

	 	CREATE TABLE students (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(50) NOT NULL,
			age tinyint(3) NOT NULL,
			email varchar(100) NOT NULL,
			mobile varchar(10) NOT NULL,
			is_removed tinyint(1) NOT NULL DEFAULT '0',
			date_created datetime NOT NULL,
			date_modified datetime DEFAULT NULL,
			PRIMARY KEY (id)
		);


		CREATE TABLE users (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			username varchar(50) NOT NULL,
			email varchar(100) NOT NULL,
			password text NOT NULL,
			is_active varchar(1) NOT NULL DEFAULT '0',
			last_in datetime DEFAULT NULL,
			login_count int(11) NOT NULL DEFAULT '0',
			date_created datetime NOT NULL,
			date_modified datetime DEFAULT NULL,
			PRIMARY KEY (id)
		);
	 */

?>
<!DOCTYPE html>
<html>
	<head>
		<?php

			session_start();

			define('VALIDATION', isset($_SESSION["validation"]) ? $_SESSION["validation"] : []);
			$input 	=	(isset($_SESSION["input"]) ? $_SESSION["input"] : []);

			if( isset($_SESSION["message"]) && !empty($_SESSION["message"]) ){
				define('MESSAGE', $_SESSION["message"]);
				unset($_SESSION["message"]);
			}

			if( isset($_SESSION["validation"]) && !empty($_SESSION["validation"]) ){
				unset($_SESSION["validation"]);
			}

			if( isset($_SESSION["input"]) && !empty($_SESSION["input"]) ){
				unset($_SESSION["input"]);
			}

			if( isset($_SESSION["auth"]) ){
				define('AUTH_USER', $_SESSION["auth"]);
			}elseif( isset($_COOKIE["auth"]) ){
				define('AUTH_USER', unserialize($_COOKIE["auth"]));
			}

			define('BASE_URL', (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off" ? "https" : "http") . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']);
			define('ASSET_FOLDER', 'assets/');

			define('ACTION_LIST', 'list');
			define('ACTION_ADD', 'add');
			define('ACTION_EDIT', 'edit');
			define('ACTION_VIEW', 'view');
			define('ACTION_SAVE', 'save');
			define('ACTION_DELETE', 'delete');
			define('ACTION_LOGIN', 'login');
			define('ACTION_CHK_LOGIN', 'checkLogin');
			define('ACTION_SIGNUP', 'signUp');
			define('ACTION_CHK_SIGNUP', 'checkSignUp');
			define('ACTION_LOGOUT', 'logout');

			define('ACTION', isset($_REQUEST['action']) ? $_REQUEST['action'] : ACTION_LIST);

			define('DB_SERVER', 'localhost');
			define('DB_USER', 'root');
			define('DB_PWD', 'letmein1!');
			define('DB', 'test');
			$tblStudent 	=	"students";
			$tblUser 	=	"users";

			$con 	=	new mysqli(DB_SERVER, DB_USER, DB_PWD, DB);

			function encryptStr($string){
				return password_hash($string, PASSWORD_DEFAULT);
			}

			function fetchStudent($columns = []){
				$con 			=	$GLOBALS["con"];
				$tblStudent 	=	$GLOBALS["tblStudent"];
				$sql 			=	"SELECT
										*
									 FROM
										$tblStudent
									 WHERE
										is_removed = 0";

				if(isset($columns["name"])){
					$sql 	.=	" AND REPLACE(LOWER(name), ' ', '') = REPLACE(LOWER('" . $columns["name"] . "'), ' ', '')";
				}

				if(isset($columns["email"])){
					$sql 	.=	" AND TRIM(LOWER(email)) = TRIM(LOWER('" . $columns["email"] . "'))";
				}

				if(isset($columns["idNot"])){
					$sql 	.=	" AND id <>" . $columns["idNot"];
				}

				$result 	=	$con->query($sql);
				$records 	=	[];

				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$records[] 		=	$row;
					}
				}

				return $records;
			}

			function fetchUser($columns = [], $ignoreStatus = false){
				$con 			=	$GLOBALS["con"];
				$tblUser 		=	$GLOBALS["tblUser"];
				$sql 			=	"SELECT
										*
									 FROM
										$tblUser
									 WHERE
										is_active IN(" . ($ignoreStatus ? "0, 1" : "1") . ")";

				if(isset($columns["usrName"])){
					$sql 	.=	" AND REPLACE(LOWER(username), ' ', '') = REPLACE(LOWER('" . $columns["usrName"] . "'), ' ', '')";
				}

				if(isset($columns["email"])){
					$sql 	.=	" AND TRIM(LOWER(email)) = TRIM(LOWER('" . $columns["email"] . "'))";
				}

				if(isset($columns["name"])){
					$sql 	.=	" AND REPLACE(LOWER(name), ' ', '') = REPLACE(LOWER('" . $columns["name"] . "'), ' ', '')";
				}

				if(isset($columns["usrNameOrEmail"])){
					$sql 	.=	" AND (username = '" . $columns["usrNameOrEmail"] . "' OR email = '" . $columns["usrNameOrEmail"] . "')";
				}

				if(isset($columns["pwd"])){
					$sql 	.=	" AND password = '" . encryptStr($columns["pwd"]) . "'";
				}

				if(isset($columns["idNot"])){
					$sql 	.=	" AND id <>" . $columns["idNot"];
				}

				$result 	=	$con->query($sql);
				$records 	=	[];

				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$records[] 		=	$row;
					}
				}

				return $records;
			}

			function updateUser($data, $userId = false){
				$con 			=	$GLOBALS["con"];
				$tblUser 		=	$GLOBALS["tblUser"];
				$modifyQuery 			=	"";

				if($userId){
					$columns 			=	"";
					$columns 		   .=	isset($data["name"]) ? "name = '" . $data["name"] . "'," : "";
					$columns 		   .=	isset($data["email"]) ? "email = '" . $data["email"] . "'," : "";
					$columns 		   .=	isset($data["lastIn"]) ? "last_in = NOW()," : "";
					$columns 		   .=	isset($data["loginCount"]) ? "login_count = login_count + 1," : "";

					$modifyQuery 		=	"UPDATE
												{$tblUser}
											 SET
												{$columns}
												date_modified = NOW()
											 WHERE
												id = " . $userId;
				}else{
					$modifyQuery 		=	"INSERT INTO
												{$tblUser}
											 (name, username, email, password, date_created)
											 VALUES
											 (
												'" . $data["name"] . "',
												'" . $data["usrName"] . "',
												'" . $data["email"] . "',
												'" . encryptStr($data["pwd"]) . "',
												NOW()
											 )";
				}

				$result 				=	$con->query($modifyQuery);
				return $result === true ? true : false;
			}

			function redirectToUrl($url){
				header("Location: " . $url);
				exit;
			}

			if(!$con->connect_error){
				if(ACTION == ACTION_CHK_LOGIN){
					$usrName 		=	isset($_REQUEST["usrName"]) ? $_REQUEST["usrName"] : null;
					$pwd 			=	isset($_REQUEST["password"]) ? $_REQUEST["password"] : null;
					$rememberMe 	=	isset($_REQUEST["rememberMe"]) ? $_REQUEST["rememberMe"] : false;

					$validation 	=	[];
					$input 			=	compact("usrName", "pwd");
					$message 		=	"";
					$isLoggedIn 	=	false;

					if(empty($usrName)){
						$validation["usrName"] = "Username should not be empty!";
					}

					if(empty($pwd)){
						$validation["pwd"] = "Password should not be empty!";
					}

					if(empty($validation)){
						$user 		=	fetchUser(["usrNameOrEmail" => $usrName]);

						if(count($user) == 1 && password_verify($pwd, $user[0]["password"])){
							$data 					=	["lastIn" => date("Y-m-d H:i:s"), "loginCount" => $user[0]["login_count"] + 1];
							updateUser($data, $user[0]["id"]);
							$isLoggedIn 			=	true;

							if($rememberMe){
								setcookie("auth", serialize($user[0]), time() + (86400 * 365), "/");
							}else{
								$_SESSION["auth"] 		=	$user[0];
							}

							$message 				=	"You are logged in successfully!";
						}else{
							$message 				=	"Please check the username and password are correct and<br> check with your administrator whether your account is enabled!";
						}
					}

					$_SESSION["message"] 	=	$message;
					$_SESSION["validation"] =	$validation;
					$_SESSION["input"] 		=	$input;

					$url 					=	BASE_URL;

					if(!(empty($validation) && $isLoggedIn)){
						$url 			   .=	"?action=" . ACTION_LOGIN;
					}

					redirectToUrl($url);
				}elseif(ACTION == ACTION_CHK_SIGNUP){
					$usrName 		=	isset($_REQUEST["usrName"]) ? $_REQUEST["usrName"] : null;
					$pwd 			=	isset($_REQUEST["password"]) ? $_REQUEST["password"] : null;
					$pwdConf 		=	isset($_REQUEST["pwdConf"]) ? $_REQUEST["pwdConf"] : null;
					$name 			=	isset($_REQUEST["name"]) ? $_REQUEST["name"] : null;
					$email 			=	isset($_REQUEST["email"]) ? $_REQUEST["email"] : null;

					$validation 	=	[];
					$input 			=	compact("usrName", "pwd", "pwdConf", "name", "email");
					$message 		=	"";
					$isAdded 		=	false;

					if(empty($usrName)){
						$validation["usrName"] = "Username should not be empty!";
					}elseif(strlen($usrName) < 4){
						$validation["usrName"] = "Username must contain at least 4 characters!";
					}elseif(!preg_match("/^[a-zA-Z0-9-_]*$/", $usrName)){
						$validation["usrName"] = "Invalid username! The username should contain only alphabets, numbers, hypen and underscore!";
					}else{
						$filter 		=	["usrName" => $usrName];

						$chkUsrName 	=	fetchUser($filter, true);

						if(count($chkUsrName) > 0){
							$validation["usrName"] =	"Username is not available";
						}
					}

					if(empty($pwd)){
						$validation["pwd"] = "Password should not be empty!";
					}elseif(!( preg_match("/[a-z]/", $pwd) && preg_match("/[A-Z]/", $pwd) && preg_match("/[0-9]/", $pwd) && preg_match("/\W/", $pwd) )){
						$validation["pwd"] = "Weak password! The password should contain at least one <ul class='square'><li>upper case letter,</li> <li>lower case letter,</li> <li>number and</li> <li>a special character!</li></ul>";
					}elseif($pwd != $pwdConf){
						$validation["pwdConf"] = "Confirm password should match with password!";
					}

					if(empty($name)){
						$validation["name"] = "Name should not be empty!";
					}elseif(!preg_match("/^[a-zA-Z\s]*$/", $name)){
						$validation["name"] = "Invalid name! The name should contain only alphabets, numbers, hypen and underscore!";
					}else{
						$filter 		=	["name" => $name];

						$chkName 		=	fetchUser($filter, true);

						if(count($chkName) > 0){
							$validation["name"] =	"Name is not available";
						}
					}

					if(empty($email)){
						$validation["email"] =	"Email should not be empty!";
					}elseif(!preg_match("/^[a-z0-9\.]+@[a-z]+\.[a-z]+$/", $email)){
						$validation["email"] =	"Invalid email!";
					}else{
						$filter 		=	["email" => $email];

						$chkEmail 		=	fetchUser($filter, true);

						if(count($chkEmail) > 0){
							$validation["email"] =	"Email is not available";
						}
					}

					if(empty($validation)){
						$isAdded 			=	updateUser($input);
						
						if($isAdded){
							$message 		=	"Your information has been registered successfully!<br>You can login to your account after the administrator has enabled your account";
						}else{
							$message 		=	"Error: " . $con->error;
						}
					}else{
						$message 			=	"Please fix the validation errors!";
					}

					$_SESSION["message"] 	=	$message;
					$_SESSION["validation"] =	$validation;
					$_SESSION["input"] 		=	$input;

					$url 					=	BASE_URL;

					if($isAdded){
						$url 			   .=	"?action=" . ACTION_LOGIN;
					}else{
						$url 			   .=	"?action=" . ACTION_SIGNUP;
					}

					redirectToUrl($url);
				}elseif(ACTION == ACTION_LOGOUT){
					if(isset($_SESSION["auth"])){
						unset($_SESSION["auth"]);
					}elseif(isset($_COOKIE["auth"])){
						unset($_COOKIE["auth"]);
						setcookie("auth", "", time() - 3600, "/");
					}

					redirectToUrl(BASE_URL);
				}
			}
		?>
		<title>
			<?php if(ACTION == ACTION_ADD): ?>
			Add | 
			<?php elseif(ACTION == ACTION_EDIT): ?>
			Edit | 
			<?php elseif(ACTION == ACTION_DELETE): ?>
			Delete | 
			<?php endif; ?>
			PHP Crud | Test programs
		</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

		<!-- Styles -->
		<link rel="stylesheet" href="<?=ASSET_FOLDER;?>materialize/css/materialize.min.css" />
		<link rel="stylesheet" href="<?=ASSET_FOLDER;?>materialize/icon/icon.css" />
		<style>
			.nav-wrapper
			{
				padding: 0 10px;
			}

			.material-icons
			{
				font-size: 18px;
			}

			.material-icons:hover
			{
				opacity: 0.7;
			}

			table tbody > tr:nth-of-type(odd)
			{
				background-color: #eee;
			}

			ul.square > li
			{
				list-style-type: square;
			}
		</style>
	</head>
	<body>

		<nav>
			<div class="nav-wrapper">
				<a href="<?=BASE_URL;?>" class="brand-logo">PHP Crud</a>
				<?php if(!$con->connect_error): ?>
				<ul class="right hide-on-med-and-down">
					<?php if(ACTION == ACTION_LIST): ?>
					<li><a href="<?=BASE_URL;?>?action=<?=ACTION_ADD;?>"><i class="material-icons left">add</i> Add</a></li>
					<?php elseif(ACTION == ACTION_LOGIN): ?>
					<li><a href="<?=BASE_URL . "?action=" . ACTION_SIGNUP;?>"><i class="material-icons left">person_add</i> Signup</a></li>
					<?php elseif(ACTION == ACTION_SIGNUP): ?>
					<li><a href="<?=BASE_URL . "?action=" . ACTION_LOGIN;?>"><i class="material-icons left">exit_to_app</i> Login</a></li>
					<?php elseif(ACTION != ACTION_LIST): ?>
					<li><a href="<?=BASE_URL;?>"><i class="material-icons left">arrow_back</i> Back</a></li>
					<?php endif; ?>
					<?php if(defined("AUTH_USER") && ACTION != ACTION_LOGOUT): ?>
					<ul id="ddUser" class="dropdown-content">
						<li><a href="<?=BASE_URL . "?action=" . ACTION_LOGOUT?>">Logout</a></li>
					</ul>
					<li><a class="dropdown-trigger" href="javascript:void(0);" data-target="ddUser">
						<?=AUTH_USER["name"];?><i class="material-icons right">arrow_drop_down</i>
					</a></li>
					<?php endif; ?>
				</ul>
				<?php endif; ?>
			</div>
		</nav>

		<div class="container">
			<div class="row">

				<?php if($con->connect_error): ?>
				<div class="section">
					<div class="card-panel red lighten-2 white-text">
						<?=$con->connect_error;?>
					</div>
				</div>
				<?php 
					elseif(!(isset($_SESSION["auth"]) || isset($_COOKIE["auth"])) && !(ACTION == ACTION_LOGIN || ACTION == ACTION_SIGNUP)): 
						redirectToUrl(BASE_URL . "?action=" . ACTION_LOGIN);
				?>
				<?php elseif(ACTION == ACTION_SAVE): ?>
				<?php
					$id 				=	isset($_REQUEST["id"]) && !empty($_REQUEST["id"]) ? base64_decode($_REQUEST["id"]) : null;
					$delete 			=	isset($_REQUEST["remove"]) ? $_REQUEST["remove"] : false;
					$name 				=	isset($_REQUEST["name"]) ? trim($_REQUEST["name"]) : null;
					$age 				=	isset($_REQUEST["age"]) ? trim($_REQUEST["age"]) : null;
					$email 				=	isset($_REQUEST["email"]) ? trim($_REQUEST["email"]) : null;
					$mobile 			=	isset($_REQUEST["mobile"]) ? trim($_REQUEST["mobile"]) : null;

					$validation 		=	[];
					$input 				=	compact("name", "age", "email", "mobile");
					$modifyQuery 		=	"";
					$message 			=	"";

					if(!$delete){
						if(empty($name) || !preg_match("/^[a-zA-Z\s]*$/", $name)){
							$validation["name"] =	"Invalid name";
						}else{
							$filter 		=	["name" => $name];

							if($id){
								$filter["idNot"] =	$id;
							}

							$chkName 		=	fetchStudent($filter);

							if(count($chkName) > 0){
								$validation["name"] =	"Name is not available";
							}
						}

						if(empty($age) || !preg_match("/^[0-9]{2}$/", $age)){
							$validation["age"] =	"Invalid age";
						}

						if(empty($email) || !preg_match("/^[a-z0-9\.]+@[a-z]+\.[a-z]+$/", $email)){
							$validation["email"] =	"Invalid email";
						}else{
							$filter 		=	["email" => $email];

							if($id){
								$filter["idNot"] =	$id;
							}

							$chkEmail 		=	fetchStudent($filter);

							if(count($chkEmail) > 0){
								$validation["email"] =	"Email is not available";
							}
						}

						if(empty($mobile) || !preg_match("/^[0-9]{10}$/", $mobile)){
							$validation["mobile"] =	"Invalid mobile";
						}
					}

					if(empty($validation)){
						if($id){
							if($delete){
								$modifyQuery 	=	"UPDATE
														$tblStudent
													 SET
														is_removed = 1,
														date_modified=NOW()
													 WHERE
														id = $id";
							}else{
								$modifyQuery 	=	"UPDATE
														$tblStudent
													 SET
														name = '$name',
														age = $age,
														email = '$email',
														mobile = '$mobile',
														date_modified = NOW()
													 WHERE
														id = $id";
							}
						}else{
							$modifyQuery 	=	"INSERT
												 INTO
													$tblStudent
													(name, age, email, mobile, date_created)
												 VALUES
													('$name', '$age', '$email', '$mobile', NOW())";
						}

						if($con->query($modifyQuery) === true){
							$message 		=	"The student information has been " . ($id ? ($delete ? "deleted" : "updated") : "added") . " successfully!";
						}else{
							$message 		=	"Error: " . $con->error;
						}
					}else{
						$message 			=	"Please fix the validation errors!";
					}

					$_SESSION["message"] 	=	$message;
					$_SESSION["validation"] =	$validation;
					$_SESSION["input"] 		=	$input;

					$url 					=	BASE_URL;

					if(!empty($validation)){
						$url 			   .=	"?action=" . ($id ? ACTION_EDIT : ACTION_ADD) . ($id ? "&id=" . base64_encode($id) : "");
					}

					redirectToUrl($url);
				?>

				<?php elseif(ACTION == ACTION_LIST): ?>
				<!-- List section -->
				<?php
					$selectQuery 		=	"SELECT
												*
											 FROM
												$tblStudent
											 WHERE
												is_removed = 0
											 ORDER BY
												name";
					$result 			=	$con->query($selectQuery);
				?>
				<div class="section">
					<h5>Students List</h5>
					<table class="highlight responsive-table bordered">
						<thead>
							<tr>
								<th>Name</th>
								<th>Age</th>
								<th>Email</th>
								<th>Phone</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php if($result->num_rows == 0): ?>
							<tr>
								<td class="center" colspan="5" align="center">No data found</td>
							</tr>
							<?php else: ?>
							<?php while($row = $result->fetch_assoc()): ?>
							<tr>
								<td><?=$row["name"];?></td>
								<td><?=$row["age"];?></td>
								<td><?=$row["email"];?></td>
								<td><?=$row["mobile"];?></td>
								<td>
									<a class="black-text" href="<?=BASE_URL;?>?action=<?=ACTION_VIEW;?>&id=<?=base64_encode($row["id"]);?>">
										<i class="material-icons">remove_red_eye</i>
									</a>
									&nbsp;
									<a href="<?=BASE_URL;?>?action=<?=ACTION_EDIT;?>&id=<?=base64_encode($row["id"]);?>">
										<i class="material-icons">edit</i>
									</a>
									&nbsp;
									<a class="small red-text" href="<?=BASE_URL;?>?action=<?=ACTION_DELETE;?>&id=<?=base64_encode($row["id"]);?>">
										<i class="material-icons">delete</i>
									</a>
								</td>
							</tr>
							<?php endwhile; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<?php elseif(ACTION == ACTION_ADD || ( isset($_REQUEST["id"]) && (ACTION == ACTION_EDIT || ACTION == ACTION_DELETE || ACTION == ACTION_VIEW) )): ?>
				<!-- Add, Edit & Delete section -->
				<div class="section">
					<?php
						$id 			=	ACTION == ACTION_EDIT || ACTION == ACTION_DELETE || ACTION == ACTION_VIEW ? base64_decode($_REQUEST["id"]) : "";
						$invalid 		=	false;
						$readonly 		=	"";

						if(empty($input)){
							if(!empty($id) && (ACTION == ACTION_EDIT || ACTION == ACTION_DELETE || ACTION == ACTION_VIEW)){
								$selectQuery 		=	"SELECT
															*
														 FROM
															$tblStudent
														 WHERE
															is_removed = 0
														 AND id = " . $id;
								$result 			=	$con->query($selectQuery);

								if($result->num_rows > 0){
									$row 				=	$result->fetch_assoc();
									$input["name"] 		=	$row["name"];
									$input["age"] 		=	$row["age"];
									$input["email"] 	=	$row["email"];
									$input["mobile"] 	=	$row["mobile"];
								}else{
									$invalid 		=	true;
								}
							}else{
								$input["name"] 		=	"";
								$input["age"] 		=	"";
								$input["email"] 	=	"";
								$input["mobile"] 	=	"";
							}
						}
					?>
					<br>
					<?php if($invalid): ?>
					<div class="card-panel red lighten-2 white-text">
						The student information does not exists in the database!
					</div>
					<?php else: ?>
					<?php if(ACTION == ACTION_ADD): ?>
					<h5>Add student details</h5>
					<?php elseif(ACTION == ACTION_EDIT): ?>
					<h5>Edit student details</h5>
					<?php elseif(ACTION == ACTION_VIEW): ?>
					<?php $readonly = "readonly=\"true\""; ?>
					<h5>View student details</h5>
					<?php endif; ?>

					<?php if(ACTION != ACTION_VIEW): ?>
					<form action="<?=BASE_URL;?>?action=<?=ACTION_SAVE;?>" method="POST">

						<input type="hidden" name="id" value="<?=base64_encode($id);?>" />
					<?php endif; ?>

						<?php if(ACTION != ACTION_DELETE): ?>
						<div class="row">
							<div class="input-field col s6">
								<input type="text" id="name" name="name" placeholder="Name" value="<?=$input["name"];?>" <?=$readonly;?> />
								<label for="name">Name</label>
								<?php if(isset(VALIDATION["name"])): ?>
								<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["name"];?></span>
								<?php endif; ?>
							</div>
							<div class="input-field col s6">
								<input type="text" id="age" name="age" placeholder="Age" value="<?=$input["age"];?>" <?=$readonly;?> />
								<label for="age">Age</label>
								<?php if(isset(VALIDATION["age"])): ?>
								<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["age"];?></span>
								<?php endif; ?>
							</div>
						</div>

						<div class="row">
							<div class="input-field col s6">
								<input type="text" id="email" name="email" placeholder="Email" value="<?=$input["email"];?>" <?=$readonly;?> />
								<label for="email">Email</label>
								<?php if(isset(VALIDATION["email"])): ?>
								<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["email"];?></span>
								<?php endif; ?>
							</div>
							<div class="input-field col s6">
								<input type="text" id="mobile" name="mobile" placeholder="Mobile" value="<?=$input["mobile"];?>" <?=$readonly;?> />
								<label for="mobile">Mobile</label>
								<?php if(isset(VALIDATION["mobile"])): ?>
								<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["mobile"];?></span>
								<?php endif; ?>
							</div>
						</div>
						<?php else: ?>
						<div class="card-panel red lighten-2 white-text">
							Are you sure you want to delete <b><?=$input["name"];?></b>'s information?
							<input type="hidden" name="remove" value="true" />
						</div>
						<?php endif; ?>

						<?php if(ACTION != ACTION_VIEW): ?>
						<div class="row">
							<button type="submit" class="btn red lighten-2">
								<?php if(ACTION == ACTION_ADD): ?>
								Add
								<?php elseif(ACTION == ACTION_EDIT): ?>
								Save
								<?php elseif(ACTION == ACTION_DELETE): ?>
								Yes
								<?php endif; ?>
							</button>
						</div>
						<?php endif; ?>

					<?php if(ACTION != ACTION_VIEW): ?>
					</form>
					<?php endif; ?>
					<?php endif; ?>
				</div>
				<?php elseif(ACTION == ACTION_LOGIN): ?>
				<!-- Login section -->
				<div class="section">
					<h5>Login</h5>
					<div class="row">
						<form action="<?=BASE_URL;?>?action=<?=ACTION_CHK_LOGIN;?>" method="POST">

							<?php
								if(empty($input)){
									$input 		=	[
														"usrName" => "",
														"pwd" => ""
													];
								}
							?>

							<div class="row">
								<div class="input-field col s6">
									<i class="material-icons prefix">account_circle</i>
									<input type="text" id="usrName" name="usrName" value="<?=$input["usrName"];?>" autocomplete="off" />
									<label for="usrName">Username or Email address</label>
									<?php if(isset(VALIDATION["usrName"])): ?>
									<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["usrName"];?></span>
									<?php endif; ?>
								</div>
							</div>

							<div class="row">
								<div class="input-field col s6">
									<i class="material-icons prefix">vpn_key</i>
									<input type="password" id="password" name="password" autocomplete="new-password" value="<?=$input["pwd"];?>" />
									<label for="password">Password</label>
									<?php if(isset(VALIDATION["pwd"])): ?>
									<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["pwd"];?></span>
									<?php endif; ?>
								</div>
							</div>

							<div class="row">
								<div class="input-field col s6" style="margin-top: 0px;">
									<label>
										<input type="checkbox" id="rememberMe" name="rememberMe" class="filled-in" />
										<span>Remember Me</span>
									</label>
								</div>
							</div>

							<div class="row" style="margin-top: 30px;">
								<div class="input-field col s6">
									<div class="col s6">
										<button type="submit" class="btn red lighten-2">
											<i class="material-icons left">exit_to_app</i> Login
										</button>
									</div>
									<div class="col s6">
										<a class="btn red lighten-2 right" href="<?=BASE_URL . "?action=" . ACTION_SIGNUP;?>">
											<i class="material-icons left">person_add</i> Go to Signup
										</a>
									</div>
								</div>
							</div>

						</form>
					</div>
				</div>
				<?php elseif(ACTION == ACTION_SIGNUP): ?>
				<!-- Signup section -->
				<div class="section">
					<h5>Signup</h5>
					<div class="row">
						<form action="<?=BASE_URL;?>?action=<?=ACTION_CHK_SIGNUP;?>" method="POST">

							<?php
								if(empty($input)){
									$input 		=	[
														"usrName" => "",
														"pwd" => "",
														"pwdConf" => "",
														"name" => "",
														"email" => ""
													];
								}
							?>

							<div class="row">
								<div class="input-field col s6">
									<i class="material-icons prefix">face</i>
									<input type="text" id="name" name="name" value="<?=$input["name"];?>" />
									<label for="name">Name</label>
									<?php if(isset(VALIDATION["name"])): ?>
									<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["name"];?></span>
									<?php endif; ?>
								</div>

								<div class="input-field col s6">
									<i class="material-icons prefix">email</i>
									<input type="text" id="email" name="email" value="<?=$input["email"];?>" />
									<label for="email">Email address</label>
									<?php if(isset(VALIDATION["pwd"])): ?>
									<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["email"];?></span>
									<?php endif; ?>
								</div>
							</div>

							<div class="row">
								<div class="input-field col s6">
									<i class="material-icons prefix">account_circle</i>
									<input type="text" id="usrName" name="usrName" value="<?=$input["usrName"];?>" />
									<label for="usrName">Username</label>
									<?php if(isset(VALIDATION["usrName"])): ?>
									<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["usrName"];?></span>
									<?php endif; ?>
								</div>
							</div>

							<div class="row">
								<div class="input-field col s6">
									<i class="material-icons prefix">vpn_key</i>
									<input type="password" class="password" id="password" name="password" autocomplete="new-password" value="<?=$input["pwd"];?>" />
									<label for="password">Password</label>
									<?php if(isset(VALIDATION["pwd"])): ?>
									<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["pwd"];?></span>
									<?php endif; ?>
								</div>

								<div class="input-field col s6">
									<i class="material-icons prefix">vpn_key</i>
									<input type="password" class="password" id="pwdConf" name="pwdConf" autocomplete="new-password" value="<?=$input["pwdConf"];?>" />
									<label for="pwdConf">Confirm password</label>
									<?php if(isset(VALIDATION["pwdConf"])): ?>
									<span class="helper-text red-text" data-error="wrong"><?=VALIDATION["pwdConf"];?></span>
									<?php endif; ?>
								</div>
							</div>

							<div class="row">
								<div class="input-field col s6" style="margin-top: 0px;">
									<label>
										<input type="checkbox" id="toggle-pwd-type" class="filled-in" />
										<span>Show Password</span>
									</label>
								</div>
							</div>

							<div class="row">
								<div class="input-field col s6" style="margin-top: 30px;">
									<div class="col s6">
										<button type="submit" class="btn red lighten-2">
											<i class="material-icons left">person_add</i> Signup
										</button>
									</div>
									<div class="col s6">
										<a class="btn red lighten-2 right" href="<?=BASE_URL . "?action=" . ACTION_LOGIN;?>">
											<i class="material-icons left">exit_to_app</i> Back to Login
										</a>
									</div>
								</div>
							</div>

						</form>
					</div>
				</div>

				<?php else: ?>
				<div class="section">
					<blockquote>
						<h1 class="red-text text-lighten-2">404</h1>
					</blockquote>
					<h4 class="red-text light text-lighten-4">Page not found</h4>
				</div>
				<?php endif; ?>
				<?php 
					if(!$con->connect_error){
						$con->close();
					}
				?>

			</div>
		</div>

		<!-- Scripts -->
		<script src="<?=ASSET_FOLDER;?>/js/jquery-3.4.1.min.js"></script>
		<script src="<?=ASSET_FOLDER;?>/materialize/js/materialize.js"></script>
		<script>
			$(document).ready(function() {

				<?php if(defined("MESSAGE")): ?>
				M.toast({
					html: <?=json_encode(MESSAGE);?>,
					displayLength: 5000
				});
				<?php endif; ?>

				<?php if(defined("AUTH_USER")): ?>
				$('.dropdown-trigger').dropdown();
				<?php endif; ?>

			});

			$(document).on('click', '#toggle-pwd-type', function(){
				$('.password').attr('type', $(this).is(':checked') ? 'text' : 'password');
			});
		</script>
	</body>
</html>
