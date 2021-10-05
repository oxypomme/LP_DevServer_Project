<?php

namespace Crisis\Actions;

use Doctrine\ORM\EntityManager;

abstract class InvokableEMAction extends InvokableJSONAction
{
  protected EntityManager $em;

  /**
   * Get the full object (public & protected props)
   * 
   * @param object $obj
   * @param string[] $excluded Excluded fields
   * @return object
   */
  protected function getFullObject(object $obj, array $excluded = []): object
  {
    $res = new \stdClass();

    $reflection = new \ReflectionClass($obj);
    $props = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

    foreach ($props as $prop) {
      $propname = $prop->name;
      if (in_array($propname, $excluded)) {
        continue;
      }

      try {
        $res->$propname = $obj->$propname;
      } catch (\Crisis\KeyNotFoundError $er) {
      }
    }

    return $res;
  }

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->em = $cnt->get(\Doctrine\ORM\EntityManager::class);
  }
}
