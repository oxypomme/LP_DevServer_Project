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

  public function __construct()
  {
    var_dump('Missing construct');
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

  public function __get(string $name)
  {
    switch ($name) {
      case 'id':
      case 'password':
        return $this->$name;
        break;

      default:
        throw new \Error("Property ${name} is not accessible");
        break;
    }
  }

  public function __set(string $name, $value)
  {
    switch ($name) {
      case 'password':
        $this->password = password_hash((string) $value, PASSWORD_DEFAULT);
        break;

      default:
        throw new \Error("Property ${name} is not accessible");
        break;
    }
  }
}
