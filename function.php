<?php

session_start();

function signup($data)
{
	$errors = array();

	//validate
	if(!preg_match('/^[a-zA-Z]+$/', $data['username'])){
		$errors[] = "Please enter a valid username";
	}

	if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL)){
		$errors[] = "Please enter a valid email";
	}

	if(strlen(trim($data['password'])) < 4){
		$errors[] = "Password must be atleast 4 chars long";
	}

	if($data['password'] != $data['password']){
		$errors[] = "Passwords must match";
	}

	$check = database_run("select * from users where email = :email limit 1",['email'=>$data['email']]);
	if(is_array($check)){
		$errors[] = "That email already exists";
	}

	//save
	if(count($errors) == 0){

		$arr['username'] = $data['username'];
		$arr['email'] = $data['email'];
		$arr['password'] = hash('sha256',$data['password']);
		$arr['date'] = date("Y-m-d H:i:s");

		$query = "insert into users (username,email,password,date) values
		(:username,:email,:password,:date)";

		database_run($query,$arr);
	}
	return $errors;
}

function login($data)
{
    $errors = array();

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email";
    }

    // Validate password length
    if (strlen(trim($data['password'])) < 4) {
        $errors[] = "Password must be at least 4 chars long";
    }

    // Check for errors
    if (count($errors) == 0) {
        $arr['email'] = $data['email'];
        $password = hash('sha256', $data['password']);

        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";

        $row = database_run($query, $arr);

        if (is_array($row)) {
            $row = $row[0];

            if ($password === $row->password) {
                $_SESSION['USER'] = $row;
                $_SESSION['LOGGED_IN'] = true;

                // Check if "Remember Me" is selected
                if (isset($data['remember'])) {
                    // Generate a unique token
                    $token = bin2hex(random_bytes(32)); // Example: 64 characters long

                    // Set a cookie with the token
                    setcookie('remember_token', $token, time() + 604800, '/'); // 604800 seconds = 7 days

                    // Update the database with the token
                    $updateTokenQuery = "UPDATE users SET remember_token = :token WHERE id = :id";
                    $updateTokenVars = array(':token' => $token, ':id' => $row->id);
                    database_run($updateTokenQuery, $updateTokenVars);
                }

            } else {
                $errors[] = "Wrong email or password";
            }

        } else {
            $errors[] = "Wrong email or password";
        }
    }

    return $errors;
}

function database_run($query,$vars = array())
{
	$string = "mysql:host=localhost;dbname=db_derma101";
	$con = new PDO($string,'root','');

	if(!$con){
		return false;
	}

	$stm = $con->prepare($query);
	$check = $stm->execute($vars);

	if($check){

		$data = $stm->fetchAll(PDO::FETCH_OBJ);

		if(count($data) > 0){
			return $data;
		}
	}

	return false;
}

function check_login($redirect = true){

	if(isset($_SESSION['USER']) && isset($_SESSION['LOGGED_IN'])){

		return true;
	}

	if($redirect){
		header("Location: login.php");
		die;
	}else{
		return false;
	}

}

function check_verified() {
    $id = $_SESSION['USER']->id;
    $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $vars = array(':id' => $id);
    $row = database_run($query, $vars);

    if (is_array($row)) {
        $row = $row[0];

        if ($row->email == $row->email_verified) {
            return true;
        }
    }

    return false;
}


