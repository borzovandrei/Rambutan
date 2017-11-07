<?php
/**
 * Created by PhpStorm.
 * User: AndreiBorzov
 * Date: 06.11.17
 * Time: 0:39
 */

namespace ShopBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ShopRepository")
 * @ORM\Table(name="likes")
 * @ORM\HasLifecycleCallbacks
 */
class Likes
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="likes")
     * @ORM\JoinColumn(name="author", referencedColumnName="id")
     */
    protected $author;


    /**
     * @ORM\ManyToOne(targetEntity="Products", inversedBy="likes")
     * @ORM\JoinColumn(name="products", referencedColumnName="id")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer", length=2)
     */
    protected $likes;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param mixed $likes
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
    }



}
