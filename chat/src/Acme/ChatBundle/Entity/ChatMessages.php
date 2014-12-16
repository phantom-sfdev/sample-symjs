<?php
namespace Acme\ChatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Acme\ChatBundle\Entity\ChatMessagesRepository")
 * @ORM\Table(name="chat_messages")
 */
class ChatMessages
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\ChatBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="integer", options={"unsigned":true, "default":0, "comment":"Timestamp when message was posted"})
     */
    protected $posted;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $msg;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set posted
     *
     * @param \DateTime $posted
     * @return ChatMessages
     */
    public function setPosted($posted)
    {
        $this->posted = $posted;

        return $this;
    }

    /**
     * Get posted
     *
     * @return \DateTime 
     */
    public function getPosted()
    {
        return $this->posted;
    }

    /**
     * Set msg
     *
     * @param string $msg
     * @return ChatMessages
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;

        return $this;
    }

    /**
     * Get msg
     *
     * @return string 
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * Set user
     *
     * @param \Acme\ChatBundle\Entity\User $user
     * @return ChatMessages
     */
    public function setUser(\Acme\ChatBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Acme\ChatBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
