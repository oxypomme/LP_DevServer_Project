<?php

namespace Crisis\Models;

use JsonSerializable;
use Doctrine\ORM\PersistentCollection;
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
  protected int $id;
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
  public \DateTime $brithdate;
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
  protected \DateTime $registerDate;

  /**
   * @OneToMany(targetEntity="Relation", mappedBy="sender")
   * @var Relation[]
   */
  protected PersistentCollection $outRelations;
  /**
   * @OneToMany(targetEntity="Relation", mappedBy="target")
   * @var Relation[]
   */
  protected PersistentCollection $inRelations;
  /**
   * @OneToMany(targetEntity="Message", mappedBy="sender")
   * @var Message[]
   */
  protected PersistentCollection $outMessages;
  /**
   * @OneToMany(targetEntity="Message", mappedBy="target")
   * @var Message[]
   */
  protected PersistentCollection $inMessages;
  /**
   * @OneToMany(targetEntity="Group", mappedBy="owner")
   * @var Group[]
   */
  protected PersistentCollection $ownedGroups;
  /**
   * @ManyToMany(targetEntity="Group", inversedBy="users")
   * @JoinTable(name="users_groups")
   * @var Group[]
   */
  protected PersistentCollection $groups;

  public function __construct()
  {
  }

  public function addOutRelation(Relation $rel)
  {
    $this->outRelations[] = $rel;
  }
  public function addInRelation(Relation $rel)
  {
    $this->inRelations[] = $rel;
  }

  public function addOutMessage(Message $msg)
  {
    $this->outMessages[] = $msg;
  }
  public function addInMessage(Message $msg)
  {
    $this->inMessages[] = $msg;
  }

  public function addOwnedGroup(Group $group)
  {
    $this->ownedGroups[] = $group;
  }
  public function addGroup(Group $group)
  {
    $this->groups[] = $group;
  }

  public function __get(string $name)
  {
    switch ($name) {
      case 'password':
        throw new \Crisis\KeyNotFoundError("Property ${name} is not accessible");
        break;

      default:
        return $this->$name;
        break;
    }
  }

  public function __set(string $name, $value)
  {
    switch ($name) {
      case 'registerDate':
      case 'id':
        throw new \Crisis\KeyNotFoundError("Property ${name} is not accessible");
        break;

      case 'password':
        $this->password = password_hash((string) $value, PASSWORD_DEFAULT);
        break;

      case 'groups':
        $value->addToGroup($this);
        $this->groups[] = $value;
        break;

      default:
        $this->$name;
        break;
    }
  }
}
