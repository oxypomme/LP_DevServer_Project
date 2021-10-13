<?php

namespace Crisis\Models;

use Doctrine\Common\Collections\Collection;

/**
 * @Entity
 * @Table(
 *  name="chat_groups"
 * )
 */
class Group
{
  /**
   * @Id 
   * @Column(type="integer") 
   * @GeneratedValue
   */
  protected int $id;
  /** 
   * @Column(type="string", unique=true) 
   */
  public string $name;
  /** 
   * @Column(type="datetime", name="creation_date", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  protected \DateTime $creationDate;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="ownedGroups", fetch="EAGER")
   */
  protected User $owner;
  /**
   * @ManyToMany(targetEntity="User", mappedBy="groups", fetch="EAGER")
   * @var User[]
   */
  protected Collection $members;

  public function __construct(string $name, User $owner)
  {
    $this->name = $name;
    $this->owner = $owner;
    $owner->addOwnedGroup($this);
    $this->creationDate = new \DateTime();
  }

  public function addToGroup(User $user)
  {
    if (!$this->members->contains($user)) {
      $this->members->add($user);
      $user->addGroup($this);
    }
  }

  public function removeToGroup(User $user)
  {
    if ($this->members->contains($user)) {
      $this->members->removeElement($user);
      $user->removeGroup($this);
    }
  }

  public function __set(string $name, $value)
  {
    switch ($name) {
      case 'creationDate':
      case 'id':
        throw new \Crisis\KeyNotFoundError("Property ${name} is not accessible");
        break;

      case 'owner':
        $value->addGroup($this);
        $this->owner = $value;
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
