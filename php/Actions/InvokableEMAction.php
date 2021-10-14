<?php

namespace Crisis\Actions;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Logging\DebugStack;

abstract class InvokableEMAction extends InvokableJSONAction
{
  protected EntityManager $em;
  protected ?DebugStack $debug_stack;

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->em = $cnt->get(\Doctrine\ORM\EntityManager::class);

    if ($_ENV['PHP_ENV'] != "production") {
      $this->debug_stack = new DebugStack();
      $this->em->getConnection()
        ->getConfiguration()
        ->setSQLLogger($this->debug_stack);
    }
  }
}
