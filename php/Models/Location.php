<?php

namespace Crisis\Models;

use JsonSerializable;

/**
 * @Entity
 * @Table(name="locations")
 */
class Location implements JsonSerializable
{
  /**
   * @Id 
   * @Column(type="integer") 
   * @GeneratedValue
   */
  public int $id;
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

  public function getUser(): User
  {
    return $this->user;
  }

  public function jsonSerialize()
  {
    $res = [
      'id' => $this->id,
      'long' => $this->long,
      'lat' => $this->lat,
      'lastUpdate' => $this->date->format('c'),
      'user' => $this->getUser()
    ];
    return $res;
  }
}
