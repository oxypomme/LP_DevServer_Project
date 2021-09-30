<?php

namespace Crisis\Providers;

use Crisis\Actions;
use UMA\DIC\Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\PhpRenderer;

/**
 * A ServiceProvider for registering services related
 * to Slim such as request handlers, routing and the
 * App service itself that wires everything together.
 */
class Slim implements \UMA\DIC\ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function provide(Container $c): void
    {
        $c->set(\Slim\App::class, static function (Container $c): \Slim\App {
            /** @var array $settings */
            $settings = $c->get('settings');

            $app = \Slim\Factory\AppFactory::create(null, $c);

            $app->addErrorMiddleware(
                $settings['slim']['displayErrorDetails'],
                $settings['slim']['logErrors'],
                $settings['slim']['logErrorDetails']
            );

            // Slim routes here

            $app->get('/', function (Request $request, Response $response, array $args) {
                $renderer = new PhpRenderer(TEMPLATES_DIR);
                return $renderer->render($response, "hello.phtml", $args);
            });

            $app->get('/protected', function (Request $request, Response $response, array $args) {
                $renderer = new PhpRenderer(TEMPLATES_DIR);
                return $renderer->render($response, "hello.phtml", $args);
            });

            $app->group('/api', function (RouteCollectorProxy $group) {
                //Group for API calls
                $group->group('/users', function (RouteCollectorProxy $group) {
                    // Group for user list
                    $group->get('', Actions\Users\ListUsers::class);
                    $group->post('', Actions\Users\NewUser::class);

                    $group->group('/{id:[0-9]+}', function (RouteCollectorProxy $group) {
                        // Group for specific user
                        $group->get('', Actions\Users\GetUser::class);
                        $group->put('', Actions\Users\UpdateUser::class);
                        $group->delete('', Actions\Users\DeleteUser::class);
                    });
                });
            })->add(\PsrJwt\Factory\JwtMiddleware::json($settings['jwt']['secret'], 'jwt', ['Auth Failed']));

            $app->group('/auth', function (RouteCollectorProxy $group) {
                $group->post('', Actions\Auth\GetJWTToken::class);
            });

            $app->get('/static/{file:.*}', function (Request $request, Response $response, $args) {
                $filePath = APP_ROOT . '/dist/' . $args['file'];

                if (!file_exists($filePath)) {
                    return $response->withStatus(404, 'File Not Found');
                }

                switch (pathinfo($filePath, PATHINFO_EXTENSION)) {
                    case 'css':
                        $mimeType = 'text/css';
                        break;

                    case 'js':
                        $mimeType = 'application/javascript';
                        break;

                        // Add more supported mime types per file extension as you need here

                    default:
                        $mimeType = 'text/html';
                }

                $newResponse = $response->withHeader('Content-Type', $mimeType . '; charset=UTF-8');

                $newResponse->getBody()->write(file_get_contents($filePath));

                return $newResponse;
            });

            return $app;
        });
    }
}
