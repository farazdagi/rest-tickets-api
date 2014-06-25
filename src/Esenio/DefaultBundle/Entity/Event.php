<?php

namespace Esenio\DefaultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * Event
 *
 * @ORM\Table(name="events")
 * @ORM\Entity(repositoryClass="Esenio\DefaultBundle\Entity\EventRepository")
 * @ExclusionPolicy("all")
 * @Gedmo\SoftDeleteable(fieldName="deleted")
 */
class Event
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
     * @ORM\ManyToOne(targetEntity="Venue")
     * @Expose
     */
    private $venue;

    /**
     * @ORM\Column(type="string")
     * @Expose
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Expose
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Expose
     */
    private $endDate;

    /**
     * @ORM\ManyToMany(targetEntity="Esenio\SecurityBundle\Entity\User")
     * @ORM\JoinTable(name="events_attendees")
     */
    private $attendees;

    /**
     * @ORM\OneToMany(targetEntity="Talk", mappedBy="event", cascade={"persist", "remove"})
     */
    private $talks;

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
     * Constructor
     */
    public function __construct()
    {
        $this->attendees = new \Doctrine\Common\Collections\ArrayCollection();
        $this->talks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Event
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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * Set venue
     *
     * @param \Esenio\DefaultBundle\Entity\Venue $venue
     * @return Event
     */
    public function setVenue(\Esenio\DefaultBundle\Entity\Venue $venue = null)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Get venue
     *
     * @return \Esenio\DefaultBundle\Entity\Venue 
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * Add attendees
     *
     * @param \Esenio\SecurityBundle\Entity\User $attendees
     * @return Event
     */
    public function addAttendee(\Esenio\SecurityBundle\Entity\User $attendees)
    {
        $this->attendees[] = $attendees;

        return $this;
    }

    /**
     * Remove attendees
     *
     * @param \Esenio\SecurityBundle\Entity\User $attendees
     */
    public function removeAttendee(\Esenio\SecurityBundle\Entity\User $attendees)
    {
        $this->attendees->removeElement($attendees);
    }

    /**
     * Get attendees
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAttendees()
    {
        return $this->attendees;
    }

    /**
     * Add talks
     *
     * @param \Esenio\DefaultBundle\Entity\Talk $talks
     * @return Event
     */
    public function addTalk(\Esenio\DefaultBundle\Entity\Talk $talks)
    {
        $this->talks[] = $talks;

        return $this;
    }

    /**
     * Remove talks
     *
     * @param \Esenio\DefaultBundle\Entity\Talk $talks
     */
    public function removeTalk(\Esenio\DefaultBundle\Entity\Talk $talks)
    {
        $this->talks->removeElement($talks);
    }

    /**
     * Get talks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTalks()
    {
        return $this->talks;
    }
}
