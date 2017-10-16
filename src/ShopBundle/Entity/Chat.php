<?php

namespace ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ChatRepository")
 * @ORM\Table(name="chat")
 * @ORM\HasLifecycleCallbacks
 */
class Chat
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id_msg;

    /**
     * @ORM\Column(type="string", length=500)
     */
    protected $message;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $author;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @var chatroom
     * @ORM\ManyToOne(targetEntity="ChatRoom", inversedBy="chat", cascade={"persist"})
     * @ORM\JoinColumn(name="chat_room", referencedColumnName="id_room")
     */
    protected $chatroom;

    /**
     * @return mixed
     */
    public function getIdMsg()
    {
        return $this->id_msg;
    }

    /**
     * @param mixed $id_msg
     */
    public function setIdMsg($id_msg)
    {
        $this->id_msg = $id_msg;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getChatroom()
    {
        return $this->chatroom;
    }

    /**
     * @param mixed $chatroom
     */
    public function setChatroom($chatroom)
    {
        $this->chatroom = $chatroom;
    }



}
