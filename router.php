<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

//API routes

if (strpos($uri, '/api/') === 0) {
    require __DIR__ . '/api/index.php';
    exit;
}

//Public
//Funcion para servir archivos estaticos (css, js, images)
$staticPath = __DIR__ . '/public' . $uri;
if (preg_match('#^/(css|js|images?)/#', $uri) && is_file($staticPath)) {
    $ext = pathinfo($staticPath, PATHINFO_EXTENSION);
    $types = [
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon'
    ];
    if (isset($types[$ext])) header('Content-Type: ' . $types[$ext]);
    readfile($staticPath);
    exit;
}


require __DIR__ . '/public/index.html';
