<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ListUsers extends InvokableEMAction
{
    public function handle(Request $request, Response $response, array $args): Response
    {
        /** @var User[] $users */
        $users = $this->em
            ->getRepository(User::class)
            ->findAll();

        return $this->createResponse($users);
    }
}
