<?php

namespace Crisis\Models;

use Doctrine\Common\Collections\Collection;

/**
 * @Entity
 * @Table(name="`groups`")
 */
class Group
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
   * @Column(type="datetime", name="creation_date", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $creationDate;

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
    $this->creationDate = new \DateTime();
  }

  public function getOwner(): User
  {
    return $this->owner;
  }
  public function setOwner(User $owner): void
  {
    $this->owner = $owner;
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
}
