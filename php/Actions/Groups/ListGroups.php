<?php

namespace Crisis\Actions\Groups;

use Crisis\Models\User;
use Crisis\Models\Group;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ListGroups extends InvokableEMAction
{
    public function handle(Request $request, Response $response, array $args): Response
    {
        /** @var Group[] $groups */
        $groups = $this->em
            ->getRepository(User::class)
            ->find((int) $args['user_id'])
            ->groups;

        $res = [];
        foreach ($groups as $group) {
            $res[] = $this->getFullObject($group);
        }

        return $this->createResponse($res);
    }
}
