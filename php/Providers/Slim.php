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
            $jwtAuthMiddleware = \PsrJwt\Factory\JwtMiddleware::json($settings['jwt']['secret'], '', ['status' => 401, 'payload' => 'Auth Failed']);

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
                        foreach ($exception->getTrace() as $key => $trace) {
                            $res['trace'][] = "#{$key}"
                                . $trace['file']
                                . '(' . $trace['line'] . '): '
                                . $trace['class']
                                . $trace['type']
                                . $trace['function'];
                        }
                    }
                    return \json_encode($res);
                });
            }

            // Slim routes here
            $app->group('/', function (RouteCollectorProxy $group) use ($renderer) {
                $group->get('', function (Request $request, Response $response, array $args) use ($renderer) {
                    return $renderer->render($response, "home.phtml", ['title' => 'Signin']);
                });
                $group->get('register', function (Request $request, Response $response, array $args) use ($renderer) {
                    return $renderer->render($response, "register.phtml", ['title' => 'Signup']);
                });
                $group->get('welcome', function (Request $request, Response $response, array $args) use ($renderer) {
                    return $renderer->render($response, "welcome.phtml", ['title' => 'Welcome']);
                });
                $group->get('messages', function (Request $request, Response $response, array $args) use ($renderer) {
                    return $renderer->render($response, "messages.phtml", ['title' => 'Messages']);
                });
                $group->post('auth[/]', Actions\Auth\GetJWTToken::class);
            });

            // Theses routes are not in group because they can't be protected by Auth
            $app->post('/api/users[/]', Actions\Users\NewUser::class);
            $app->get('/api[/]', function (Request $request, Response $response, array $args) use ($renderer) {
                return $renderer->render($response, "api_doc.phtml", ['title' => 'Documentation']);
            });

            $app->get('/auth[/]', Actions\Auth\CheckJWTToken::class)->add($jwtAuthMiddleware);
            $app->group('/api', function (RouteCollectorProxy $group) use ($app) {
                //Group for API calls
                $group->group('/users', function (RouteCollectorProxy $group) {
                    // Group for user list
                    $group->get('[/]', Actions\Users\ListUsers::class);

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
                            // Group for user's messages
                            $group->get('[/]', Actions\Users\Messages\ListMessages::class);

                            $group->get('/{message_id:[0-9]+}[/]', Actions\Users\Messages\GetMessage::class);
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

            return $app;
        });
    }
}
