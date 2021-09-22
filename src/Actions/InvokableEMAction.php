<?php

namespace Crisis\Actions;

use Doctrine\ORM\EntityManager;

abstract class InvokableEMAction extends InvokableAction
{
  protected EntityManager $em;

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->em = $cnt->get(\Doctrine\ORM\EntityManager::class);
  }
}
