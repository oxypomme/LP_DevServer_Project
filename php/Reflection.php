<?php

namespace Crisis;

abstract class Reflection
{
  /**
   * Get the full object (public & protected props)
   * 
   * @param object $obj
   * @param string[] $excluded Excluded fields
   * @return object
   */
  static function getFullObject(object $obj, array $excluded = []): object
  {
    $res = new \stdClass();

    $reflection = new \ReflectionClass($obj);
    $props = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

    foreach ($props as $prop) {
      $propname = $prop->name;
      if (in_array($propname, array_merge($excluded))) {
        continue;
      }

      try {
        $prop->setAccessible(true);
        $res->$propname = $obj->$propname;
        $prop->setAccessible(false);
      } catch (\Crisis\KeyNotFoundError $er) {
      }
    }

    return $res;
  }
}
