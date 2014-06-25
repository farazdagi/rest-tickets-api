<?php

namespace Esenio\DefaultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * Talk
 *
 * @ORM\Table(name="talks")
 * @ORM\Entity(repositoryClass="Esenio\DefaultBundle\Entity\TalkRepository")
 * @Gedmo\SoftDeleteable(fieldName="deleted")
 * @ExclusionPolicy("all")
 */
class Talk
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @Expose
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Esenio\SecurityBundle\Entity\User")
     * @Expose
     */
    private $speaker;

    /**
     * @ORM\Column(type="string")
     * @Expose
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Expose
     */
    private $abstract;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deleted;

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
     * Set title
     *
     * @param string $title
     * @return Talk
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set abstract
     *
     * @param string $abstract
     * @return Talk
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Get abstract
     *
     * @return string 
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Talk
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Talk
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set deleted
     *
     * @param \DateTime $deleted
     * @return Talk
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return \DateTime 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set event
     *
     * @param \Esenio\DefaultBundle\Entity\Event $event
     * @return Talk
     */
    public function setEvent(\Esenio\DefaultBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \Esenio\DefaultBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set speaker
     *
     * @param \Esenio\SecurityBundle\Entity\User $speaker
     * @return Talk
     */
    public function setSpeaker(\Esenio\SecurityBundle\Entity\User $speaker = null)
    {
        $this->speaker = $speaker;

        return $this;
    }

    /**
     * Get speaker
     *
     * @return \Esenio\SecurityBundle\Entity\User 
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }
}
