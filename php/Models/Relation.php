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
        if (!property_exists($this, $name)) {
          throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
        }
        $this->$name = $value;
        break;
    }
  }
}
