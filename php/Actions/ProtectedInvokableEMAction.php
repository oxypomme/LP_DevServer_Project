<?php

namespace Crisis\Actions;

use Crisis\Models\User;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Crisis\Actions\InvokableEMAction;

abstract class ProtectedInvokableEMAction extends InvokableEMAction
{
  /**
   * Check if current user exists, and if the current user is the same as the requested user.
   * If no requested user, just checking if current user exists
   * 
   * @param Request $request Current Request
   * @param int $requested_user_id Requested user's id
   * @throws HttpException If permission is denied
   * @return int Current user's id
   */
  public function checkUser(Request $request, int $requested_user_id = null): int
  {
    $jwt = (new \PsrJwt\Helper\Request())->getParsedToken($request, '');
    $auth_user_id = (int) $jwt->getSubject();

    $user = $this->em
      ->getRepository(User::class)
      ->find($auth_user_id);

    $res = !is_null($user);
    if (!is_null($requested_user_id)) {
      $res = $res && $auth_user_id == $requested_user_id;
    }


    if (!$res) {
      throw new HttpException($request, 'Unauthorized', 401);
    }
    return $auth_user_id;
  }
}
