<?php

namespace Crisis\Actions;

use Crisis\Models\User;
use Doctrine\ORM\EntityManager;
use Nyholm\Psr7;
use function json_encode;

class ListUsers implements \Psr\Http\Server\RequestHandlerInterface
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        /** @var User[] $users */
        $users = $this->em
            ->getRepository(User::class)
            ->findAll();

        $body = Psr7\Stream::create(json_encode($users, JSON_PRETTY_PRINT) . PHP_EOL);

        return new Psr7\Response(
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Length' => $body->getSize()
            ],
            $body
        );
    }
}