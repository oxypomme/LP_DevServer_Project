<?php

namespace Crisis\Actions;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;

abstract class ProtectedInvokableEMAction extends InvokableEMAction
{
  /**
   * Check if current user exists, and if the current user is the same as the requested user.
   * If no requested user, just checking if current user exists
   * 
   * @param int $auth_user_id Current user's id
   * @param int $requested_user_id Requested user's id
   */
  public function checkUser(int $auth_user_id, int $requested_user_id = null): bool
  {
    // TODO: Expire date

    $user = $this->em
      ->getRepository(User::class)
      ->find($auth_user_id);

    $res = !is_null($user);
    if (!is_null($requested_user_id)) {
      $res = $res && $auth_user_id == $requested_user_id;
    }

    return $res;
  }
}
