<?php

namespace Crisis\Models;

/**
 * @Entity
 * @Table(name="relations")
 */
class Relation
{
  /**
   * @Id 
   * @Column(type="integer") 
   * @GeneratedValue
   */
  protected $id;
  /** 
   * @Column(type="datetime") 
   */
  public \DateTime $date;
  /**
   * @ManyToOne(targetEntity="User", inversedBy="outRelations")
   */
  protected $sender;
  /**
   * @ManyToOne(targetEntity="User", inversedBy="inRelations")
   */
  protected $target;

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
      case 'sender':
        $value->addOutRelation($this);
        $this->sender = $value;
        break;
      case 'target':
        $value->addInRelation($this);
        $this->target = $value;
        break;

      default:
        throw new \Error("Property ${name} is not accessible");
        break;
    }
  }
}
