<?php
declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params(['httponly'=>true,'secure'=>isset($_SERVER['HTTPS']),'samesite'=>'Lax','path'=>'/']);
    session_start();
}
function owner_logged_in(): bool { start_secure_session(); return ($_SESSION['role'] ?? '') === 'owner'; }
function require_owner(): void { if (!owner_logged_in()) { header('Location: /?page=login'); exit; } }
function csrf_token(): string { start_secure_session(); return $_SESSION['csrf'] ??= bin2hex(random_bytes(24)); }
function verify_csrf(): void { start_secure_session(); if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Invalid session token.'); } }
