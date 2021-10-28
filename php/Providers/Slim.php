<?php

namespace Crisis\Providers;

use Crisis\Actions;
use UMA\DIC\Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
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

            $app = \Slim\Factory\AppFactory::create(null, $c);

            $errorMiddleware = $app->addErrorMiddleware(
                $settings['slim']['displayErrorDetails'],
                $settings['slim']['logErrors'],
                $settings['slim']['logErrorDetails']
            );

            // Minify HTML if production
            if ($_ENV['PHP_ENV'] == "production") {
                $app->add(new \Slim\Middleware\Minify());
            }

            // Prepare JWT
            // $jwtAuthMiddleware = \PsrJwt\Factory\JwtMiddleware::json($settings['jwt']['secret'], '', ['status' => 401, 'payload' => 'Auth Failed']);
            $jwtAuthMiddleware = new \PsrJwt\JwtAuthMiddleware(new \Crisis\Handlers\JWTHandler($settings['jwt']['secret']));
            // Prepare CSRF
            session_start();
            $responseFactory = $app->getResponseFactory();
            $c->set('csrf', function () use ($responseFactory) {
                $guard = new \Slim\Csrf\Guard($responseFactory);
                $guard->setFailureHandler(function (Request $request, Handler $handler) use ($responseFactory) {
                    $response = $responseFactory->createResponse();
                    $body = $response->getBody();
                    $body->write(\Crisis\JSON::encode([
                        'status' => 400,
                        'payload' => 'Failed CSRF check'
                    ]));
                    return $response
                        ->withStatus(400)
                        ->withHeader('Content-Type', 'application/json')
                        ->withBody($body);
                });
                return $guard;
            });
            $csrfMiddleware = $c->get('csrf');
            // Adding CORS
            $app->options('/{routes:.+}', fn (Request $request, Response $response, array $args) => $response);
            $corsMiddleware = function (Request $request, Handler $handler) {
                $response = $handler->handle($request);
                return $response
                    ->withHeader('Access-Control-Allow-Origin', 'https://crisis.fr') // Production Server
                    ->withHeader('Vary', 'Origin')
                    ->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            };
            $app->add($corsMiddleware);

            // Matching Slim Errors to Base Response
            $errorHandler = $errorMiddleware->getDefaultErrorHandler();
            if ($errorHandler instanceof \Slim\Handlers\ErrorHandler) {
                $errorHandler->registerErrorRenderer('application/json', function (\Throwable $exception, bool $displayErrorDetails): string {
                    $res = [
                        'status' => $exception->getCode(),
                        'payload' => $exception->getMessage()
                    ];
                    if ($displayErrorDetails) {
                        $res['trace'] = [];
                        foreach ($exception->getTrace() as $trace) {
                            $argstr = '(';
                            $i = 0;
                            foreach ($trace['args'] as $arg) {
                                if ($i > 0) {
                                    $argstr .= ', ';
                                }
                                switch (gettype($arg)) {
                                    case 'object':
                                        $argstr .= get_class($arg);
                                        break;
                                    case 'boolean':
                                    case 'integer':
                                    case 'double':
                                    case 'string':
                                        $argstr .= $arg;
                                        break;
                                    case 'NULL':
                                        $argstr .= "NULL";
                                        break;
                                    default:
                                        $argstr .= gettype($arg);
                                        break;
                                }
                                $i++;
                            }
                            $argstr .= ')';

                            $res['trace'][] =
                                $trace['file']
                                . '(' . $trace['line'] . '): '
                                . $trace['class']
                                . $trace['type']
                                . $trace['function']
                                . $argstr;
                        }
                    }
                    return \Crisis\JSON::encode($res);
                });
            }

            // Public routes (not protected by Auth or by CSRF)
            $app->post('/auth[/]', Actions\Auth\GetJWTToken::class);
            $app->post('/api/users[/]', Actions\Users\NewUser::class);

            // Client routes here (CSRF protected routes)
            $app->group('/', function (RouteCollectorProxy $group) use ($c) {
                $group->get('[/]', new Actions\RenderAction($c, "home", ['title' => 'Signin', 'nonav' => true]));
                $group->get('register[/]', new Actions\RenderAction($c, "register", ['title' => 'Signup', 'nonav' => true]));
                $group->get('welcome[/]', new Actions\RenderAction($c, "welcome", ['title' => 'Welcome']));
                $group->get('messages[/]', new Actions\RenderAction($c, "messages", ['title' => 'Messages']));
                $group->get('board[/]', new Actions\RenderAction($c, "board", ['title' => 'Board']));
                $group->get('account[/]', new Actions\RenderAction($c, "account", ['title' => 'My Account']));
                $group->get('map[/]', new Actions\RenderAction($c, "map", ['title' => 'Map']));
                $group->get('search[/]', new Actions\RenderAction($c, "search", ['title' => 'Search']));
                $group->get('api[/]', new Actions\RenderAction($c, "api_doc", ['title' => 'Documentation', 'nonav' => true]));
            })->add($csrfMiddleware);

            // Auth protected routes
            $app->get('/auth[/]', Actions\Auth\CheckJWTToken::class)->add($jwtAuthMiddleware);
            $app->group('/api', function (RouteCollectorProxy $group) {
                //Group for API calls
                $group->group('/users', function (RouteCollectorProxy $group) {
                    // Group for user list
                    $group->get('[/]', Actions\Users\ListUsers::class);
                    $group->get('/me[/]', Actions\Users\GetCurrentUser::class);

                    $group->group('/{user_id:[0-9]+}', function (RouteCollectorProxy $group) {
                        // Group for specific user
                        $group->get('[/]', Actions\Users\GetUser::class);
                        $group->put('[/]', Actions\Users\UpdateUser::class);
                        $group->delete('[/]', Actions\Users\DeleteUser::class);

                        $group->group('/groups', function (RouteCollectorProxy $group) {
                            // Group for user's groups
                            $group->get('[/]', Actions\Users\Groups\ListUserGroups::class);

                            $group->group('/{group_id:[0-9]+}', function (RouteCollectorProxy $group) {
                                // Group for specific user's group
                                $group->post('[/]', Actions\Users\Groups\AddUserGroup::class);
                                $group->get('[/]', Actions\Users\Groups\GetUserGroup::class);
                                $group->delete('[/]', Actions\Users\Groups\RemoveUserGroup::class);
                            });
                        });

                        $group->group('/relations', function (RouteCollectorProxy $group) {
                            // Group for user's relations
                            $group->get('[/]', Actions\Users\Relations\ListRelations::class);
                            $group->post('[/]', Actions\Users\Relations\NewRelation::class);

                            $group->group('/{relation_id:[0-9]+}', function (RouteCollectorProxy $group) {
                                // Group for specific user's relation
                                $group->get('[/]', Actions\Users\Relations\GetRelation::class);
                                $group->delete('[/]', Actions\Users\Relations\DeleteRelation::class);
                            });
                        });

                        $group->group('/location', function (RouteCollectorProxy $group) {
                            // Group for user's location
                            $group->get('[/]', Actions\Users\Location\GetUserLocation::class);
                            $group->post('[/]', Actions\Users\Location\NewUserLocation::class);
                            $group->put('[/]', Actions\Users\Location\UpdateUserLocation::class);
                            $group->delete('[/]', Actions\Users\Location\DeleteUserLocation::class);
                        });

                        $group->group('/messages', function (RouteCollectorProxy $group) {
                            $group->group('/{target_id:[0-9]+}', function (RouteCollectorProxy $group) {
                                // Group for user's messages against target
                                $group->get('[/]', Actions\Users\Messages\ListMessages::class);

                                $group->get('/{message_id:[0-9]+}[/]', Actions\Users\Messages\GetMessage::class);
                            });
                        });
                    });
                });

                $group->group('/groups', function (RouteCollectorProxy $group) {
                    // Group for groups
                    // $group->get('[/]', Actions\Groups\ListGroups::class);
                    $group->post('[/]', Actions\Groups\NewGroup::class);

                    $group->group('/{group_id:[0-9]+}', function (RouteCollectorProxy $group) {
                        // Group for specific group
                        $group->get('[/]', Actions\Groups\GetGroup::class);
                        $group->put('[/]', Actions\Groups\UpdateGroup::class);
                        $group->delete('[/]', Actions\Groups\DeleteGroup::class);

                        $group->group('/members', function (RouteCollectorProxy $group) {
                            // Group for members of a group
                            $group->get('[/]', Actions\Groups\Members\ListMembers::class);

                            $group->group('/{group_user_id:[0-9]+}', function (RouteCollectorProxy $group) {
                                // Group for specific user in a group
                                $group->get('[/]',  Actions\Groups\Members\GetMember::class);
                                $group->delete('[/]', Actions\Groups\Members\DeleteMember::class);
                            });
                        });

                        $group->group('/messages', function (RouteCollectorProxy $group) {
                            // Group for messages of a group
                            $group->get('[/]', Actions\Groups\Messages\ListGroupMessages::class);

                            $group->get('/{message_id:[0-9]+}[/]', Actions\Groups\Messages\GetGroupMessage::class);
                        });
                    });
                });
            })->add($jwtAuthMiddleware);

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

            $app->get('/favicon.ico', function (Request $request, Response $response, $args) {
                $filePath = APP_ROOT . '/dist/res/favicon.ico';
                if (!file_exists($filePath)) {
                    return $response->withStatus(404, 'File Not Found');
                }

                $newResponse = $response; //->withHeader('Content-Type', $mimeType . '; charset=UTF-8');
                $newResponse->getBody()->write(file_get_contents($filePath));

                return $response;
            });

            /**
             * Catch-all route to serve a 404 Not Found page if none of the routes match
             * NOTE: make sure this route is defined last
             */
            $app->map(['GET', 'POST', 'PUT', 'DELETE'], '/{routes:.+}', function ($request, $response) {
                throw new \Slim\Exception\HttpNotFoundException($request);
            });

            return $app;
        });
    }
}
