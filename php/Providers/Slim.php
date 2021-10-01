<?php

namespace Crisis\Providers;

use Crisis\Actions;
use UMA\DIC\Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteCollectorProxy;

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
            $renderer = $c->get(\Slim\Views\PhpRenderer::class);

            $app = \Slim\Factory\AppFactory::create(null, $c);

            $app->addErrorMiddleware(
                $settings['slim']['displayErrorDetails'],
                $settings['slim']['logErrors'],
                $settings['slim']['logErrorDetails']
            );

            // Minify HTML if production
            if ($_ENV['PHP_ENV'] == "production") {
                $app->add(new \Slim\Middleware\Minify());
            }

            // Slim routes here


            $app->group('/', function (RouteCollectorProxy $group) use ($renderer) {
                $group->get('', function (Request $request, Response $response, array $args) use ($renderer) {
                    return $renderer->render($response, "home.phtml", ['title' => 'Signin']);
                });
                $group->get('register', function (Request $request, Response $response, array $args) use ($renderer) {
                    return $renderer->render($response, "register.phtml", ['title' => 'Signup']);
                });
                $group->post('auth', Actions\Auth\GetJWTToken::class);
            });

            $app->group('/api', function (RouteCollectorProxy $group) {
                // TODO: API Doc
                // $group->get('', ...)
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
            })->add(\PsrJwt\Factory\JwtMiddleware::json($settings['jwt']['secret'], 'jwt', ['stauts' => 401, 'message' => 'Auth Failed']));


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
