<?php

namespace Crisis\Models;

/**
 * @Entity
 * @Table(name="locations")
 */
class Location
{
  /**
   * @Id 
   * @Column(type="integer") 
   * @GeneratedValue
   */
  protected int $id;
  /** 
   * @Column(type="float") 
   */
  public float $long;
  /** 
   * @Column(type="float") 
   */
  public float $lat;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $lastUpdate;

  /**
   * @OneToOne(targetEntity="User")
   */
  public User $user;

  public function __get(string $name)
  {
    if (!property_exists($this, $name)) {
      throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
    }

    switch ($name) {
      default:
        return $this->$name;
        break;
    }
  }

  public function __set(string $name, $value)
  {
    if (!property_exists($this, $name)) {
      throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
    }

    switch ($name) {
      case 'id':
        throw new \Crisis\KeyNotFoundError("Property ${name} is not accessible");
        break;

      default:
        $this->$name = $value;
        break;
    }
  }
}
