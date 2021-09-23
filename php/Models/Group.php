<?php

namespace Crisis\Models;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(
 *  name="groups"
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
   * @Column(type="string") 
   */
  public string $name;
  /** 
   * @Column(type="datetime", name="creation_date", options={"default": "CURRENT_TIMESTAMP"}) 
   */
  protected \DateTime $creationDate;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="ownedGroups")
   */
  protected User $owner;
  /**
   * @ManyToMany(targetEntity="User", mappedBy="groups")
   * @var User[]
   */
  protected ArrayCollection $users;

  public function __construct()
  {
    $this->users = new ArrayCollection();
  }

  public function addToGroup(User $user)
  {
    $users[] = $user;
  }

  public function __get(string $name)
  {
    switch ($name) {
      default:
        return $this->$name;
        break;
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
        $this->$name = $value;
        break;
    }
  }
}
