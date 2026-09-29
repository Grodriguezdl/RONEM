<?php
/**
 * google/config.php
 *
 * Copia este archivo como config.php y coloca las credenciales OAuth de tu proyecto
 * en Google Cloud Console. config.php está excluido de Git.
 */
require_once __DIR__ . '/../../vendor/autoload.php';

$client = new Google\Client();

$client->setClientId('TU_CLIENT_ID.apps.googleusercontent.com');
$client->setClientSecret('TU_CLIENT_SECRET');

$client->setRedirectUri(
    'http://localhost/RONEM/auth/google/google_callback.php'
);
 

/**
 * SCOPES
 */
$client->addScope('email');
$client->addScope('profile');

/**
 * CONFIG EXTRA IMPORTANTE
 */
$client->setAccessType('offline');
$client->setPrompt('select_account');