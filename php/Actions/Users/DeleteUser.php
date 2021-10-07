<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DeleteUser extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], (int) $args['user_id'])) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    try {
      //TODO: Remove user from relations
      //TODO: Remove user from groups
      //TODO: Remove user from locations
      //TODO: Remove user from messages
      $this->em->remove($user);
      $this->em->flush();
    } catch (\Exception $e) {
      $this->em->rollback();
      throw $e;
    }

    return $this->createResponse(['status' => 200, 'message' => 'OK']);
  }
}
