<?php
$path = '.env';
$content = file_get_contents($path);
$content = preg_replace('/^APP_URL=.*$/m', 'APP_URL=http://localhost:8000', $content);
$content = preg_replace('/^SESSION_DOMAIN=.*$/m', 'SESSION_DOMAIN=localhost', $content);
if (strpos($content, 'SANCTUM_STATEFUL_DOMAINS') === false) {
    $content .= "\nSANCTUM_STATEFUL_DOMAINS=localhost:3000";
} else {
    $content = preg_replace('/^SANCTUM_STATEFUL_DOMAINS=.*$/m', 'SANCTUM_STATEFUL_DOMAINS=localhost:3000', $content);
}
if (strpos($content, 'SESSION_SECURE_COOKIE') === false) {
    $content .= "\nSESSION_SECURE_COOKIE=false";
} else {
    $content = preg_replace('/^SESSION_SECURE_COOKIE=.*$/m', 'SESSION_SECURE_COOKIE=false', $content);
}
file_put_contents($path, $content);
echo "Done";
