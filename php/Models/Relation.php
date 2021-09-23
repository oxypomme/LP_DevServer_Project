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
  protected int $id;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  protected \DateTime $date;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="outRelations")
   */
  protected User $sender;
  /**
   * @ManyToOne(targetEntity="User", inversedBy="inRelations")
   */
  protected User $target;

  public function __get(string $name)
  {
    switch ($name) {
      default:
        return $this->$name;
        break;
    }
  }

  public function __set(string $name, $value)
  {
    switch ($name) {
      case 'date':
      case 'id':
        throw new \Crisis\KeyNotFoundError("Property ${name} is not accessible");
        break;

      case 'sender':
        $value->addOutRelation($this);
        $this->sender = $value;
        break;
      case 'target':
        $value->addInRelation($this);
        $this->target = $value;
        break;

      default:
        $this->$name = $value;
        break;
    }
  }
}
