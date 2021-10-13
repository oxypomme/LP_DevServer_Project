<?php

namespace Crisis\Actions;

use Doctrine\ORM\EntityManager;

abstract class InvokableEMAction extends InvokableJSONAction
{
  protected EntityManager $em;

  /**
   * @deprecated Use \Crisis\Reflection::getFullObject instead, or just don't use it
   */
  protected function getFullObject(object $obj, array $excluded = []): object
  {
    return \Crisis\Reflection::getFullObject($obj, $excluded);
  }

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->em = $cnt->get(\Doctrine\ORM\EntityManager::class);
  }
}
