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
  public \DateTime $created_at;

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
    $this->created_at = new \DateTime();
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

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'sender' => $this->getSender(),
      'target' => $this->getTarget(),
      'created_at' => $this->created_at->format('c')
    ];
  }
}
