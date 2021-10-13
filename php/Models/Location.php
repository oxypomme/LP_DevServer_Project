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
  public \DateTime $updated_at;

  /**
   * @OneToOne(targetEntity="User", fetch="EAGER")
   */
  protected User $user;

  public function __construct(float $long, float $lat, User $user)
  {
    $this->long = $long;
    $this->lat = $lat;
    $this->updated_at = new \DateTime();
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
      'user' => $this->getUser(),
      'updated_at' => $this->updated_at->format('c')
    ];
    return $res;
  }
}
