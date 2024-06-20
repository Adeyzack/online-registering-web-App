<?php
$password = 'Password1!'; //insert your own admin password here
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
echo $hashed_password;
?>
