<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /YAKAP/auth/login.php');
exit;
