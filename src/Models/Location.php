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
   * @Column(type="datetime") 
   */
  public \DateTime $lastUpdate;

  /**
  * @OneToOne(targetEntity="User")
  */
  public User $user;

  public function __get(string $name)
  {
    switch ($name) {
      case 'id':
        return $this->$name;
        break;

      default:
        throw new \Error("Property ${name} is not accessible");
        break;
    }
  }

  public function __set(string $name, $value)
  {
    switch ($name) {
      default:
        throw new \Error("Property ${name} is not accessible");
        break;
    }
  }
}
