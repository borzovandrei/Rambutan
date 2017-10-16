<?php
namespace ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ChatRepository")
 * @ORM\Table(name="chatroom")
 * @ORM\HasLifecycleCallbacks
 */

class ChatRoom
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id_room;


    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @var Chat
     * @ORM\OneToMany(targetEntity="Chat", mappedBy="chatroom")
     */
    protected $chat;

    /**
     * @return mixed
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param mixed $chat
     */
    public function setChat($chat)
    {
        $this->chat = $chat;
    }





    /**
     * @return mixed
     */
    public function getIdRoom()
    {
        return $this->id_room;
    }

    /**
     * @param mixed $id_room
     */
    public function setIdRoom($id_room)
    {
        $this->id_room = $id_room;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }



}
