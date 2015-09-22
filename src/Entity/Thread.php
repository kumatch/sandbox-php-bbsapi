<?php
namespace Kumatch\BBSAPI\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Thread
 *
 * @ORM\Entity(repositoryClass="Kumatch\BBSAPI\Repository\ThreadRepository")
 * @ORM\Table(name="thread")
 * @ORM\HasLifecycleCallbacks
 */
class Thread
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=40, nullable=false)
     * @Assert\NotBlank
     * @Assert\Regex(pattern="/^[\s]+$/", match=false)
     * @Assert\Length(max=40)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="Kumatch\BBSAPI\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_posted_at", type="datetime", nullable=true)
     */
    private $lastPostedAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Kumatch\BBSAPI\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="threads_tags",
     *      joinColumns={ @ORM\JoinColumn(name="thread_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="tag_id", referencedColumnName="id") }
     * )
     */
    private $tags;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }
    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \DateTime|null $lastPostedAt
     * @return $this
     */
    public function setLastPostedAt(\DateTime $lastPostedAt = null)
    {
        $this->lastPostedAt = $lastPostedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastPostedAt()
    {
        return $this->lastPostedAt;
    }

    /**
     * @param Tag $tag
     * @return $this
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags->toArray();
    }
}