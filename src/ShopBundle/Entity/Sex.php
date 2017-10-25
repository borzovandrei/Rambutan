<?php
namespace ShopBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ShopRepository")
 * @ORM\Table(name="sex")
 * @ORM\HasLifecycleCallbacks
 */
class Sex
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
     * @ORM\OneToMany(targetEntity="ShopBundle\Entity\Users", mappedBy="sex")
     */
    protected $id_sex;




    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id_sex = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Sex
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add idSex
     *
     * @param \ShopBundle\Entity\Users $idSex
     *
     * @return Sex
     */
    public function addIdSex(\ShopBundle\Entity\Users $idSex)
    {
        $this->id_sex[] = $idSex;

        return $this;
    }

    /**
     * Remove idSex
     *
     * @param \ShopBundle\Entity\Users $idSex
     */
    public function removeIdSex(\ShopBundle\Entity\Users $idSex)
    {
        $this->id_sex->removeElement($idSex);
    }

    /**
     * Get idSex
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdSex()
    {
        return $this->id_sex;
    }
}
