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
   * @Column(name="`long`", type="float") 
   */
  protected float $long;
  /** 
   * @Column(type="float") 
   */
  protected float $lat;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $updated_at;

  /**
   * @OneToOne(targetEntity="User", inversedBy="location", fetch="EAGER")
   * @JoinColumn(name="user_id", referencedColumnName="id")
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

  public function update(float $long = null, float $lat = null): self
  {
    if (!is_null($long)) {
      $this->long = $long;
    }
    if (!is_null($lat)) {
      $this->lat = $lat;
    }
    if (!is_null($long) || !is_null($lat)) {
      $this->updated_at = new \DateTime();
    }
    return $this;
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
