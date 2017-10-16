<?php

namespace ShopBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ShopRepository")
 * @ORM\Table(name="orders")
 * @ORM\HasLifecycleCallbacks
 */
class Order
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;


    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $status;


    /**
     * @ORM\Column(type="float")
     */
    protected $price;


    /**
     * @ORM\Column(type="string")
     */
    protected $map;


    /**
     *
     * @ORM\ManyToMany(targetEntity="Products", inversedBy="id_order")
     * @ORM\JoinTable(name="order_products")
     */
    protected $id_products;

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param mixed $map
     */
    public function setMap($map)
    {
        $this->map = $map;
    }

    /**
     * @return mixed
     */
    public function getIdProducts()
    {
        return $this->id_products;
    }

    /**
     * @param mixed $id_products
     */
    public function setIdProducts($id_products)
    {
        $this->id_products = $id_products;
    }








}