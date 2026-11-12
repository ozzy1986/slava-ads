<?php

declare(strict_types=1);

/**
 * Lead intake entry point. Thin: wires dependencies, delegates to the
 * controller, emits the response.
 */

use SlavaAds\App;
use SlavaAds\Database\Connection;
use SlavaAds\Http\Request;
use SlavaAds\Http\Responder;
use SlavaAds\Http\Response;

/** @var \SlavaAds\Config\Env $env */
$env = require __DIR__ . '/bootstrap.php';

$responder = new Responder();
$request = Request::fromGlobals($env->get('TRUST_PROXY_HEADERS') === '1');

// Lightweight challenge for the form: signed timestamp + self-hosted CAPTCHA.
// Served without touching the database so it stays fast under spam load.
if ($request->method() === 'GET' && isset($_GET['challenge'])) {
    $body = [
        'ok' => true,
        'token' => App::formToken($env)->issue(),
    ];

    $captcha = App::captcha($env);
    if ($captcha !== null) {
        $challenge = $captcha->issue();
        $body['captcha'] = [
            'token' => $challenge['token'],
            'image' => $challenge['image'],
        ];
    }

    $responder->emit(new Response(200, $body));
    return;
}

try {
    $pdo = Connection::fromEnv($env);
    $controller = App::leadController($env, $pdo);
    $response = $controller->handle($request);
} catch (Throwable $e) {
    error_log('[lead] fatal: ' . $e->getMessage());
    $response = Response::error('Временная ошибка. Попробуйте ещё раз.', 500);
}

$responder->emit($response);
