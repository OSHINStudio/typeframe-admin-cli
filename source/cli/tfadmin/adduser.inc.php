<?php
/**
 * Create a new user account.
 * This command is interactive. It will prompt you for the user name, email,
 * and other information.
 */

$valid = false;
while (!$valid) {
	echo "User name:";
	$username = trim(fgets(STDIN));
	if ($username) {
		$users = new Model_User();
		$users->where('username = ?', $username);
		$user = $users->getFirst();
		if ($user->exists()) {
			echo "That name is already taken.\n";
		} else {
			$valid = true;
		}
	}
}

$valid = false;
while (!$valid) {
	echo "Email:";
	$email = trim(fgets(STDIN));
	if ($email) {
		$field = new Form_Field_Email();
		if ($field->validate($email, 'email')) {
			$users = new Model_User();
			$users->where('email = ?', $email);
			$user = $users->getFirst();
			if ($user->exists()) {
				echo "That address is already in use.\n";
			} else {
				$valid = true;
			}
		} else {
			echo $field->error() . "\n";
		}
	}
}

$valid = false;
while (!$valid) {
	echo "User group:";
	$usergroup = trim(fgets(STDIN));
	if ($usergroup) {
		$usergroups = new Model_Usergroup();
		$usergroups->where('usergroupname = ?', $usergroup);
		$usergroup = $usergroups->getFirst();
		if ($usergroup->exists()) {
			$usergroupid = $usergroup['usergroupid'];
			$valid = true;
		} else {
			echo "That user group does not exist.\n";
		}
	}
}

$valid = false;
while (!$valid) {
	echo "Password:";
	$password = trim(fgets(STDIN));
	if ($password) {
		$valid = true;
	}
}

$user = Model_User::Create();
$user['username'] = $username;
$user['email'] = $email;
$user['usergroupid'] = $usergroupid;
$user['password'] = $password;
$user['confirmed'] = 1;
$user->save();

echo "User account created.\n";
