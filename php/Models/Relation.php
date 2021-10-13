<?php

namespace Crisis\Models;

use JsonSerializable;

/**
 * @Entity
 * @Table(
 *  name="relations",
 *  uniqueConstraints={@UniqueConstraint(columns={"sender_id", "target_id"})}
 * )
 */
class Relation implements JsonSerializable
{
  /**
   * @Id 
   * @Column(type="integer") 
   * @GeneratedValue
   */
  public int $id;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $date;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="outRelations", fetch="EAGER")
   */
  protected User $sender;
  /**
   * @ManyToOne(targetEntity="User", inversedBy="inRelations", fetch="EAGER")
   */
  protected User $target;

  public function __construct(User $sender, User $target)
  {
    $this->date = new \DateTime();
    $this->sender = $sender;
    $sender->addOutRelation($this);
    $this->target = $target;
    $target->addInRelation($this);
  }

  public function getSender(): User
  {
    return $this->sender;
  }
  public function getTarget(): User
  {
    return $this->target;
  }

  public function jsonSerialize()
  {
    $res = [
      'id' => $this->id,
      'date' => $this->date->format('c'),
      'sender' => $this->getSender(),
      'target' => $this->getTarget()
    ];
    return $res;
  }
}
