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
   * @OneToOne(targetEntity="User", fetch="EAGER")
   */
  protected User $user;

  public function __construct(float $long, float $lat, User $user)
  {
    $this->long = $long;
    $this->lat = $lat;
    $this->lastUpdate = new \DateTime();
    $this->user = $user;
    $user->setLocation($this);
  }

  public function __get(string $name)
  {
    if (!property_exists($this, $name)) {
      throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
    }
    return $this->$name;
  }
}
