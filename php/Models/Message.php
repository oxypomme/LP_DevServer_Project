<?php

namespace Crisis\Models;

/**
 * @Entity
 * @Table(name="messages")
 */
class Message
{
  /** 
   * @Id
   * @Column(type="integer")
   * @GeneratedValue
   */
  protected int $id;
  /** 
   * @Column(type="string", columnDefinition="TEXT NOT NULL") 
   */
  public string $content;
  /** 
   * @Column(type="string", columnDefinition="TEXT", nullable=true) 
   */
  public string $attachement;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  protected \DateTime $date;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $edit_date;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="outMessages")
   */
  protected User $sender;
  /**
   * @ManyToOne(targetEntity="User", inversedBy="inMessages")
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
        $value->addOutMessage($this);
        $this->sender = $value;
        break;
      case 'target':
        $value->addInMassage($this);
        $this->target = $value;
        break;

      default:
        $this->$name = $value;
        break;
    }
  }
}
