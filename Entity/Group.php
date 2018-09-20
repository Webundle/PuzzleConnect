<?php

namespace Puzzle\ConnectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * Group
 *
 * @ORM\Table(name="user_group")
 * @ORM\Entity(repositoryClass="Puzzle\ConnectBundle\Repository\GroupRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Group
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Puzzle\ConnectBundle\Service\IdGenerator")
     */
    protected $id;
    
    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="groups_users",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $users;
    
    public function __construct(){
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getName() {
        return $this->name;
    }
    
    public function setDescription($description) : self {
        $this->description = $description;
        return $this;
    }
    
    public function getDescription() :? string {
        return $this->description;
    }

    public function setUsers(Collection $users) : self {
        foreach ($users as $user){
            $this->adduser($user);
        }
        
        return $this;
    }
    
    public function addUser(User $user) : self {
        if (!$this->users->contains($user)){
            $this->users->add($user);
        }
        
        return $this;
    }
    
    public function removeUser(User $user) : self {
        if ($this->users->contains($user)){
            $this->users->removeElement($user);
        }
        
        return $this;
    }
    
    public function getUsers() :? Collection {
    	return $this->users;
    }
}
