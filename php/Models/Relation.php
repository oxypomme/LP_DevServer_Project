<?php

namespace Crisis\Models;

/**
 * @Entity
 * @Table(
 *  name="relations",
 *  uniqueConstraints={@UniqueConstraint(columns={"sender_id", "target_id"})}
 * )
 */
class Relation
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

  public function __get(string $name)
  {
    if (!property_exists($this, $name)) {
      throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
    }
    return $this->$name;
  }
}
