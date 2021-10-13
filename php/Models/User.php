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
    $this->password = password_hash((string) $password, PASSWORD_DEFAULT);
    $this->email = $email;
    $this->phone = $phone;
    $this->birthdate = $birthdate;
    $this->address = $address;
    $this->city = $city;
    $this->country = $country;
    $this->status = \Crisis\EStatus::SAFE;
    $this->registerDate = new DateTime();
  }

  public function setLocation(Location $loc)
  {
    if ($this->location != $loc) {
      $this->location = $loc;
    }
  }

  public function addOutRelation(Relation $rel)
  {
    if (!$this->outRelations->contains($rel)) {
      $this->outRelations->add($rel);
    }
  }
  public function removeOutRelation(Relation $rel)
  {
    if ($this->outRelations->contains($rel)) {
      $this->outRelations->add($rel);
    }
  }
  public function addInRelation(Relation $rel)
  {
    if (!$this->inRelations->contains($rel)) {
      $this->inRelations->removeElement($rel);
    }
  }
  public function removeInRelation(Relation $rel)
  {
    if ($this->inRelations->contains($rel)) {
      $this->inRelations->removeElement($rel);
    }
  }

  public function addOutMessage(Message $msg)
  {
    if ($this->outMessages->contains($msg)) {
      $this->outMessages->add($msg);
    }
  }
  public function addInMessage(Message $msg)
  {
    if ($this->inMessages->contains($msg)) {
      $this->inMessages->add($msg);
    }
  }

  public function addOwnedGroup(Group $group)
  {
    if (!$this->ownedGroups->contains($group)) {
      $this->ownedGroups->add($group);
    }
  }
  public function removeOwnedGroup(Group $group)
  {
    if ($this->ownedGroups->contains($group)) {
      $this->ownedGroups->removeElement($group);
    }
  }

  public function addGroup(Group $group)
  {
    if (!$this->groups->contains($group)) {
      $this->groups->add($group);
      $group->addToGroup($this);
    }
  }
  public function removeGroup(Group $group)
  {
    if ($this->groups->contains($group)) {
      $this->groups->removeElement($group);
      $group->removeToGroup($this);
    }
  }

  public function __get(string $name)
  {
    if (!property_exists($this, $name)) {
      throw new \Crisis\KeyNotFoundError("Property ${name} doen't exists");
    }
    return $this->$name;
  }
}
