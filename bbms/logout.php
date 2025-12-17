<?php
session_start();
session_destroy();
header("Location: /bbms/login.php");
exit;
?>
