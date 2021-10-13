<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetUser extends InvokableEMAction
{
    public function handle(Request $request, Response $response, array $args): Response
    {
        /** @var User $user */
        $user = $this->em
            ->getRepository(User::class)
            ->find((int) $args['user_id']);

        return $this->createResponse($user);
    }
}
