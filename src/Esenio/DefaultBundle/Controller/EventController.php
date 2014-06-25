<?php

namespace Esenio\DefaultBundle\Controller;

use Gedmo\Exception\RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Util\Codes;

use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View as RestView;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;
use Esenio\DefaultBundle\Entity\EventRepository;
use Esenio\DefaultBundle\Entity\Event;
use Esenio\DefaultBundle\Form\EventType;


/**
 * /secured/events Entry Point
 *
 * @package Esenio\DefaultBundle\Controller
 */
class EventController extends Controller implements ClassResourceInterface
{
    /**
     * @var EventRepository
     * @DI\Inject("esenio_default.event_repository")
     */
    private $repository;

    /**
     * Gets list of events.
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Gets list of active events.",
     *  filters={
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="count", "dataType"="integer"}
     *  }
     * )
     *
     * @Rest\View()
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page number.")
     * @QueryParam(name="count", requirements="\d+", default="3",  description="Item count limit")
     *
     * @return RestView
     */
    public function cgetAction(Request $request, ParamFetcher $paramFetcher)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($this->repository->getEventsQueryBuilder()));
        $pager->setCurrentPage($paramFetcher->get('page'));
        $pager->setMaxPerPage($paramFetcher->get('count'));

        return iterator_to_array($pager->getCurrentPageResults());
    }

    /**
     * Allow pre-flight requests to /events.
     * @Rest\Options("/events")
     * @return RestView
     */
    public function optionsEventsAction()
    {
        return $this->view(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Allow pre-flight requests to /event/:id.
     * @Rest\Options("/events/{id}")
     * @param $id
     * @return RestView
     */
    public function optionsEventAction($id)
    {
        return $this->view(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Get event details.
     *
     * @param int $id
     * @Rest\View()
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return null|object
     */
    public function getAction($id)
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }

    /**
     * Adds event to collection.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *  description="Creates new event",
     *  input="Esenio\DefaultBundle\Form\EventType",
     *  output="Esenio\DefaultBundle\Entity\Event"
     * )
     *
     * @return array|RestView
     * @throws \RuntimeException
     */
    public function cpostAction(Request $request)
    {
        $entity = new Event();
        $form = $this->createForm(new EventType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl('get_event', array('id' => $entity->getId()) ),
                Codes::HTTP_CREATED
            );
        }

        return array('form' => $form);
    }

    /**
     * Updates event
     * @param Request $request
     * @param $id
     * @return array|RestView
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function putAction(Request $request, $id)
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new EventType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->view(null, Codes::HTTP_NO_CONTENT);
        }

        return array('form' => $form);
    }

    /**
     * Deletes event.
     *
     * @param $id
     * @return RestView
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction($id)
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        return $this->view(null, Codes::HTTP_NO_CONTENT);
    }


    /**
     * @param ParamFetcher $paramFetcher
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page number.")
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function getTalksAction(ParamFetcher $paramFetcher, $id)
    {
        /** @var Event $entity */
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $pager = new Pagerfanta(new DoctrineCollectionAdapter($entity->getTalks()));
        $pager->setCurrentPage($paramFetcher->get('page'));
        $pager->setMaxPerPage(3);

        return $pager->getCurrentPageResults();
    }
}
