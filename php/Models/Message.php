<?php

namespace Crisis\Models;

use JsonSerializable;

/**
 * @Entity
 * @Table(name="messages")
 */
class Message implements JsonSerializable
{
  /** 
   * @Id
   * @Column(type="integer")
   * @GeneratedValue
   */
  public int $id;
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
  public \DateTime $date;
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
    $this->content = $content;
    $this->attachement = $attachement;
    $this->date = new \DateTime();
    $this->edit_date = new \DateTime();
    $this->sender = $sender;
    $sender->addOutMessage($this);
    $this->target = $target;
    $target->addInMessage($this);
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
      'content' => $this->content,
      'date' => $this->date->format('c'),
      'edit_date' => $this->edit_date->format('c'),
      'sender' => $this->getSender(),
      'target' => $this->getTarget()
    ];
    return $res;
  }
}
