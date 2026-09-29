<?php
/**
 * google/google_login.php
 */
require_once 'config.php';

$authUrl = $client->createAuthUrl();

header("Location: " . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();