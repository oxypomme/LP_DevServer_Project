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
  public ?string $attachement;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $created_at;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $updated_at;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="outMessages", fetch="EAGER")
   */
  protected User $sender;
  /**
   * @ManyToOne(targetEntity="User", inversedBy="inMessages", fetch="EAGER")
   *  @ORM\JoinColumn(name="target_id", referencedColumnName="id", nullable=false)
   */
  protected ?User $target;
  /**
   * @ManyToOne(targetEntity="Group", inversedBy="messages", fetch="EAGER")
   *  @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
   */
  protected ?Group $group;

  public function __construct(string $content, string $attachement, User $sender, User $target = null, Group $group = null)
  {
    $this->content = $content;
    $this->attachement = $attachement;
    $this->created_at = new \DateTime();
    $this->updated_at = new \DateTime();
    $this->sender = $sender;
    $sender->addOutMessage($this);
    if ($target) {
      $this->target = $target;
      $target->addInMessage($this);
    }
    if ($group) {
      $this->group = $group;
      $group->addMessage($this);
    }
  }

  public function getSender(): User
  {
    return $this->sender;
  }
  public function getTarget(): ?User
  {
    return $this->target;
  }
  public function getGroup(): ?Group
  {
    return $this->group;
  }

  public function jsonSerialize(): array
  {
    $res = [
      'id' => $this->id,
      'content' => $this->content,
      'sender' => $this->getSender(),
      // 'group' => $this->getGroup(),
      'created_at' => $this->created_at->format('c'),
      'updated_at' => $this->updated_at->format('c')
    ];
    if ($this->attachement) {
      $res['attachement'] = $this->attachement;
    }
    if ($this->getTarget()) {
      $res['target'] = $this->getTarget();
    }
    return $res;
  }
}
