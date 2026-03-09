<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Task03\Database;
use Task03\GameRepository;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$pdo = Database::connect(__DIR__ . '/../db/app.sqlite');
$repository = new GameRepository($pdo);

$app->get('/', function (Request $request, Response $response) {
    $htmlPath = __DIR__ . '/index.html';
    $response->getBody()->write((string) file_get_contents($htmlPath));

    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
});

$app->get('/games', function (Request $request, Response $response) use ($repository) {
    return jsonResponse($response, [
        'games' => $repository->getGames(),
    ]);
});

$app->get('/games/{id}', function (Request $request, Response $response, array $args) use ($repository) {
    $id = (int) ($args['id'] ?? 0);
    $game = $repository->getGame($id);

    if ($game === null) {
        return jsonResponse($response->withStatus(404), [
            'error' => 'Игра не найдена.',
        ]);
    }

    return jsonResponse($response, $game);
});

$app->post('/games', function (Request $request, Response $response) use ($repository) {
    $data = (array) $request->getParsedBody();
    $playerName = trim((string) ($data['playerName'] ?? ''));

    if ($playerName === '') {
        return jsonResponse($response->withStatus(400), [
            'error' => 'Нужно указать имя игрока.',
        ]);
    }

    $game = $repository->createGame($playerName);

    return jsonResponse($response->withStatus(201), $game);
});

$app->post('/step/{id}', function (Request $request, Response $response, array $args) use ($repository) {
    $id = (int) ($args['id'] ?? 0);
    $data = (array) $request->getParsedBody();

    if (!array_key_exists('answer', $data) || filter_var($data['answer'], FILTER_VALIDATE_INT) === false) {
        return jsonResponse($response->withStatus(400), [
            'error' => 'Ответ должен быть целым числом.',
        ]);
    }

    try {
        $result = $repository->saveStep($id, (int) $data['answer']);
    } catch (RuntimeException $exception) {
        $message = $exception->getMessage();
        $status = $message === 'Игра не найдена.' ? 404 : 400;

        return jsonResponse($response->withStatus($status), [
            'error' => $message,
        ]);
    }

    return jsonResponse($response, $result);
});

$app->run();

function jsonResponse(Response $response, array $payload): Response
{
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );

    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
}
