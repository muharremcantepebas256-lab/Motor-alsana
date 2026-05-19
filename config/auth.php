<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION["user_id"]);
}

function is_admin(): bool
{
    return is_logged_in();
}

function require_login(): void
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        die("Bu sayfaya erisim yetkiniz yok.");
    }
}
