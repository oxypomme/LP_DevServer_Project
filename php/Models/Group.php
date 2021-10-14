<?php

namespace Crisis\Models;

use Doctrine\Common\Collections\Collection;
use JsonSerializable;

/**
 * @Entity
 * @Table(name="`groups`")
 */
class Group implements JsonSerializable
{
  /**
   * @Id 
   * @Column(type="integer") 
   * @GeneratedValue
   */
  public int $id;
  /** 
   * @Column(type="string", unique=true) 
   */
  public string $name;
  /** 
   * @Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $created_at;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="ownedGroups", fetch="EAGER")
   */
  protected User $owner;
  /**
   * @ManyToMany(targetEntity="User", mappedBy="groups", fetch="EAGER")
   * @var Collection<int, User> $members
   */
  protected Collection $members;

  public function __construct(string $name, User $owner)
  {
    $this->name = $name;
    $this->owner = $owner;
    $owner->addOwnedGroup($this);
    $this->created_at = new \DateTime();
  }

  public function getOwner(): User
  {
    return $this->owner;
  }
  public function setOwner(User $owner): void
  {
    $this->owner = $owner;
  }

  /** @return User[] */
  public function getMembers(): array
  {
    return $this->members->getValues();
  }
  public function addToGroup(User $user): void
  {
    if (!$this->members->contains($user)) {
      $this->members->add($user);
      $user->addGroup($this);
    }
  }
  public function removeToGroup(User $user): void
  {
    if ($this->members->contains($user)) {
      $this->members->removeElement($user);
      $user->removeGroup($this);
    }
  }

  public function jsonSerialize()
  {
    $res = [
      'id' => $this->id,
      'name' => $this->name,
      'owner' => $this->getOwner(),
      'created_at' => $this->created_at->format('c')
    ];
    return $res;
  }
}
