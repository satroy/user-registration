<?php require('includes/config.php');

//logout
$user->user_logout();

//logged in return to index page
header('Location: index.php');
exit;
?>