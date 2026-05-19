<?php
require_once __DIR__ . "/config/auth.php";

session_unset();
session_destroy();

header("Location: index.php");
exit;
?>
