<?php
namespace ShopBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ShopRepository")
 * @ORM\Table(name="measure")
 * @ORM\HasLifecycleCallbacks
 */
class Measure
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $name;


    /**
     * @ORM\OneToMany(targetEntity="ShopBundle\Entity\Products", mappedBy="measure")
     */
    protected $id_measure;

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

    /**
     * @return mixed
     */
    public function getIdSort()
    {
        return $this->id_sort;
    }

    /**
     * @param mixed $id_sort
     */
    public function setIdSort($id_sort)
    {
        $this->id_sort = $id_sort;
    }


}