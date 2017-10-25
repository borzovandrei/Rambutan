<?php

namespace ShopBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ShopRepository")
 * @ORM\Table(name="products")
 * @ORM\HasLifecycleCallbacks
 */
class Products
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank()
     */
    protected $name;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;

    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;


    /**
     * @ORM\ManyToOne(targetEntity="Sort", inversedBy="id_sort")
     * @ORM\JoinColumn(name="sort_id", referencedColumnName="id")
     */
    protected $id_class;


    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    protected $balanse;


    /**
     * @ORM\ManyToOne(targetEntity="Measure", inversedBy="id_measure")
     * @ORM\JoinColumn(name="measure_id", referencedColumnName="id")
     */
    protected $measure;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    protected $price;


    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    protected $shop_price;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="product")
     */
    protected $comments;

    /**
     * @return integer
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
     * @return string
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
    public function getShopPrice()
    {
        return $this->shop_price;
    }

    /**
     * @param mixed $shop_price
     */
    public function setShopPrice($shop_price)
    {
        $this->shop_price = $shop_price;
    }

    /**
     * @return mixed
     */
    public function getIdClass()
    {
        return $this->id_class;
    }

    /**
     * @param mixed $id_class
     */
    public function setIdClass($id_class)
    {
        $this->id_class = $id_class;
    }

    /**
     * @return mixed
     */
    public function getBalanse()
    {
        return $this->balanse;
    }

    /**
     * @param mixed $balanse
     */
    public function setBalanse($balanse)
    {
        $this->balanse = $balanse;
    }


    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__ . '/../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'img/products';
    }


    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return mixed
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * @param mixed $measure
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;
    }


    /**
     * Add comment
     *
     * @param \ShopBundle\Entity\Comment $comment
     *
     * @return Products
     */
    public function addComment(\ShopBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \ShopBundle\Entity\Comment $comment
     */
    public function removeComment(\ShopBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }


    public function __toString()
    {
        return $this->getName();
    }


    public function upload()
    {

        if (null === $this->getFile()) {
            return;
        }
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );
        $this->path = $this->getFile()->getClientOriginalName();
        $this->file = null;
    }

}
