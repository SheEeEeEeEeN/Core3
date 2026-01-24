<?php
session_start();
session_unset();
session_destroy();
include('loading.html');
header("Location: login.php");
exit();
?>