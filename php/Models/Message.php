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
   * @Column(type="text") 
   */
  public string $content;
  /** 
   * @Column(type="text", nullable=true) 
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
   * @ManyToOne(targetEntity="User", inversedBy="outMessages", fetch="EAGER")
   */
  protected User $sender;
  /**
   * @ManyToOne(targetEntity="User", inversedBy="inMessages", fetch="EAGER")
   */
  protected User $target;

  public function __construct(string $content, string $attachement, User $sender, User $target)
  {
    $this->conent = $content;
    $this->attachement = $attachement;
    $this->date = new \DateTime();
    $this->edit_date = new \DateTime();
    $this->sender = $sender;
    $this->target = $target;
  }

  public function __get(string $name)
  {
    if (!property_exists($this, $name)) {
      throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
    }

    switch ($name) {
      default:
        return $this->$name;
        break;
    }
  }

  public function __set(string $name, $value)
  {
    if (!property_exists($this, $name)) {
      throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
    }

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
