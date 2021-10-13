<?php

namespace Crisis\Models;

use DateTime;
use JsonSerializable;
use Doctrine\Common\Collections\Collection;
use function password_hash;

/**
 * @Entity
 * @Table(name="users")
 */
class User
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
  public string $username;
  /** 
   * @Column(type="string") 
   */
  protected string $password;
  /** 
   * @Column(type="string", unique=true) 
   */
  public string $email;
  /** 
   * @Column(type="string") 
   */
  public string $phone;
  /** 
   * @Column(type="datetime") 
   */
  public \DateTime $birthdate;
  /** 
   * @Column(type="string") 
   */
  public string $address;
  /** 
   * @Column(type="string") 
   */
  public string $city;
  /** 
   * @Column(type="string") 
   */
  public string $country;
  /** 
   * @Column(type="integer", options={"default": 0})
   */
  public int $status;
  /** 
   * @Column(type="datetime", name="register_date", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  public \DateTime $registerDate;

  /**
   * @OneToOne(targetEntity="Location", fetch="EAGER", cascade={"remove"})
   */
  // protected Location $location;

  /**
   * @OneToMany(targetEntity="Relation", mappedBy="sender", fetch="EAGER", cascade={"remove"})
   * @var Collection<int, Relation> $outRelations
   */
  protected Collection $outRelations;
  /**
   * @OneToMany(targetEntity="Relation", mappedBy="target", fetch="EAGER", cascade={"remove"})
   * @var Collection<int, Relation> $inRelations
   */
  protected Collection $inRelations;
  /**
   * @OneToMany(targetEntity="Message", mappedBy="sender", fetch="EAGER", cascade={"remove"})
   * @var Collection<int, Message> $outMessages
   */
  protected Collection $outMessages;
  /**
   * @OneToMany(targetEntity="Message", mappedBy="target", fetch="EAGER", cascade={"remove"})
   * @var Collection<int, Message> $inMessages
   */
  protected Collection $inMessages;
  /**
   * @OneToMany(targetEntity="Group", mappedBy="owner", fetch="EAGER", cascade={"remove"})
   * @var Collection<int, Group> $ownedGroups
   */
  protected Collection $ownedGroups;
  /**
   * @ManyToMany(targetEntity="Group", inversedBy="members", fetch="EAGER")
   * @JoinTable(name="users_groups")
   * @var Collection<int, Group> $groups
   */
  protected Collection $groups;

  public function __construct(string $username, string $password, string $email, string $phone, DateTime $birthdate, string $address, string $city, string $country)
  {
    $this->username = $username;
    $this->setPassword($password);
    $this->email = $email;
    $this->phone = $phone;
    $this->birthdate = $birthdate;
    $this->address = $address;
    $this->city = $city;
    $this->country = $country;
    $this->status = \Crisis\EStatus::SAFE;
    $this->registerDate = new DateTime();
  }

  public function setLocation(Location $loc): void
  {
    if ($this->location != $loc) {
      $this->location = $loc;
    }
  }

  public function setPassword(string $password): void
  {
    $this->password = password_hash((string) $password, PASSWORD_DEFAULT);
  }

  public function checkPassword(string $password): bool
  {
    return password_verify($password, $this->password);
  }

  /** @return Relation[] */
  public function getMergedRelations(): array
  {
    return array_merge($this->outRelations->getValues(), $this->inRelations->getValues());
  }
  public function getRelations(): array
  {
    return [
      'outRelations' => $this->outRelations->getValues(),
      'inRelations' => $this->inRelations->getValues()
    ];
  }

  public function addOutRelation(Relation $rel): void
  {
    if (!$this->outRelations->contains($rel)) {
      $this->outRelations->add($rel);
    }
  }
  public function removeOutRelation(Relation $rel): void
  {
    if ($this->outRelations->contains($rel)) {
      $this->outRelations->add($rel);
    }
  }

  public function addInRelation(Relation $rel): void
  {
    if (!$this->inRelations->contains($rel)) {
      $this->inRelations->removeElement($rel);
    }
  }
  public function removeInRelation(Relation $rel): void
  {
    if ($this->inRelations->contains($rel)) {
      $this->inRelations->removeElement($rel);
    }
  }

  /** @return Message[] */
  public function getMergedMessages(): array
  {
    return array_merge($this->outMessages->getValues(), $this->inMessages->getValues());
  }
  public function getMessages(): array
  {
    return [
      'outMessages' => $this->outMessages->getValues(),
      'inMessages' => $this->inMessages->getValues()
    ];
  }

  public function addOutMessage(Message $msg): void
  {
    if ($this->outMessages->contains($msg)) {
      $this->outMessages->add($msg);
    }
  }
  public function addInMessage(Message $msg): void
  {
    if ($this->inMessages->contains($msg)) {
      $this->inMessages->add($msg);
    }
  }

  /** @return Group[] */
  public function getMergedGroups(): array
  {
    return array_merge($this->ownedGroups->getValues(), $this->groups->getValues());
  }
  public function getGroups(): array
  {
    return [
      'ownedGroups' => $this->ownedGroups->getValues(),
      'groups' => $this->groups->getValues()
    ];
  }

  public function addOwnedGroup(Group $group): void
  {
    if (!$this->ownedGroups->contains($group)) {
      $this->ownedGroups->add($group);
    }
  }
  public function removeOwnedGroup(Group $group): void
  {
    if ($this->ownedGroups->contains($group)) {
      $this->ownedGroups->removeElement($group);
    }
  }

  public function addGroup(Group $group): void
  {
    if (!$this->groups->contains($group)) {
      $this->groups->add($group);
      $group->addToGroup($this);
    }
  }
  public function removeGroup(Group $group): void
  {
    if ($this->groups->contains($group)) {
      $this->groups->removeElement($group);
      $group->removeToGroup($this);
    }
  }
}
