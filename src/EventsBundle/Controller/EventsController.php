<?php


namespace EventsBundle\Controller;


use EventsBundle\Entity\Events;
use EventsBundle\Entity\Feed;
use EventsBundle\Entity\Participe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends Controller
{

    public function AjouterEventAction( \Symfony\Component\HttpFoundation\Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = new Events();
        $form = $this->createForm('EventsBundle\Form\EventsType', $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $event->setNomfile("3.jpg");
            $event->getUploadFile();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('event_Afficher');
        }
        return $this->render('EventsBundle:Events:AjouterEvent.html.twig', array(
            'form' => $form->createView(),

        ));
    }


    public function AfficheEventsFrontAction()
    {
        $m = $this->getDoctrine()->getManager();
        $event = $m->getRepository("EventsBundle:Events")->findAll();
        return $this->render('EventsBundle:Events:AfficherEventsFront.html.twig', array(
            'event' => $event
        ));
    }


    public function AfficheEventAction()
    {
        $m = $this->getDoctrine()->getManager();
        $event = $m->getRepository("EventsBundle:Events")->findAll();
        return $this->render('EventsBundle:Events:AfficherEvent.html.twig', array(
            'event' => $event
        ));
    }



    public function deleteEventAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $Pro = $em->getRepository('EventsBundle:Events')->find($id);
        $em->remove($Pro);
        $em->flush();


        return $this->redirectToRoute('event_Afficher');
    }



    public function ModifierEventAction(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository('EventsBundle:Events')->find($id);
        $editForm = $this->createForm('EventsBundle\Form\EventsType', $event);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('event_Afficher');
        }
        $em = $this->getDoctrine()->getManager();

        return $this->render('EventsBundle:Events:ModifierEvent.html.twig', array(
            'Events' => $event,
            'form' => $editForm->createView(),
        ));
    }





    /** *************************************************************************************************************************************** **/




    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $evenements = $em->getRepository('EventsBundle:Events')->Top5();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($evenements, $request->query->getInt('page', 1), 5);
        return $this->render('EventsBundle:Events:index.html.twig', array(
            'R' => $pagination
        ));
    }

    public function ShowEventsAction()
    {
        $m = $this->getDoctrine()->getManager();
        $event = $m->getRepository("EventsBundle:Events")->findAll();
        return $this->render('EventsBundle:Events:ShowEvent.html.twig', array(
            'event' => $event
        ));
    }



    public function ShowEventsFrontAction()
    {
        $m = $this->getDoctrine()->getManager();
        $event = $m->getRepository("EventsBundle:Events")->findAll();
        return $this->render('EventsBundle:Events:AfficherEventsFront.html.twig', array(
            'event' => $event
        ));
    }



    public function newAction(Request $request)
    {
        $evenement = new Events();
        $form = $this->createForm('EventsBundle\Form\EventsType', $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();


            $evenement->setNomfile("3.jpg");
            $evenement->getUploadFile();

            $evenement->setNbrParticipant(0);

            if ( $evenement->getDateFin() < $evenement->getDateDebut())
            {
                $em->persist($evenement);
                $em->flush();

            }


            return $this->redirectToRoute('event_Show');
        }
        return $this->render('EventsBundle:Events:AjouterEvent2.html.twig', array(
            'form' => $form->createView(),
        ));
    }









    public function showAction(Events $evenement, \Symfony\Component\HttpFoundation\Request $request)
    {
        $user = $this->getUser()->getId();

        $d = new \DateTime('now');


        //Affiche feedback
        $em = $this->getDoctrine()->getManager();


        //Participer
        $IsParticiped = $em->getRepository('EventsBundle:Participe')->IsParticiped($evenement->getId(), $user);

//dumppppppppppppppppppppppppppp
//        var_dump($IsParticiped);
        if ($request->getMethod() == 'POST') {
            if ($request->get('part') && $evenement->getDateFin() > $d) {
                if ($IsParticiped <1) {
                    $p = new Participe();
                    $emParticipe = $this->getDoctrine()->getManager();
                    $p->setIdEvent($evenement->getId());
                    $p->setIdUser($user);
                    $emParticipe->persist($p);
                    $emParticipe->flush();

                    $NbrParticipant = $em->getRepository('EventsBundle:Participe')->NbrParticipantParEvent($evenement->getId());


                    $evenement->setNbrParticipant($NbrParticipant);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($evenement);
                    $em->flush();
                    var_dump($NbrParticipant);
                }
            }
        }

        $feed = new Feed();

        // Ajout FeedBack
        $evenement_id = $evenement->getId();

        $feed_input = $request->request->get('test');

        if ($request->getMethod() == 'POST'  && $feed_input != "" && $evenement->getDateFin() < $d ) {

                $feed->setIdUser($user);
                $feed->setIdEvent($evenement_id);
                $feed->setDescription($feed_input);
                $feed->setDateFeed(new \DateTime('now'));
        }




        $em = $this->getDoctrine()->getManager();
        $em->persist($feed);
        $em->flush();

//        $deleteForm = $this->createDeleteForm($evenement);
        $feedEvent = $em->getRepository('EventsBundle:Feed')->findFeedbyEvent($evenement->getId());

        return $this->render('EventsBundle:Events:show.html.twig', array(
            'evenement' => $evenement,
//            'delete_form' => $deleteForm->createView(),
            'feedEvent' => $feedEvent
        ));
    }








    public function AjoutAction(Events $evenement,$id)
    {
        $user = $this->getUser()->getId();
        $d = new \DateTime('now');
        $em = $this->getDoctrine()->getManager();
        $evenement = $em->getRepository("EventsBundle:Events")->findOneByid($id);
        $events_id = $evenement->getId();

        $commentaire = new Feed();

        $contenu =$_POST['contenu'];

        if ($contenu != "" && $evenement->getDateFin() > $d )
        {

        $commentaire->setIdEvent($events_id);
        $commentaire->setDescription($contenu);
        $commentaire->setIdUser($user);
        $commentaire->setDateFeed(new \DateTime('now'));

        $em=$this->getDoctrine()->getManager();
        $em->persist($commentaire);
        $em->flush();

        }

        return $this->redirect($this->generateUrl('one_event_Show',array('id'=>$id)));
    }





    public function ShowCommentsAction()
    {
        $m = $this->getDoctrine()->getManager();
        $feed = $m->getRepository("EventsBundle:Feed")->AllFeeds();
        return $this->render('EventsBundle:Events:AfficherFeeds.html.twig', array(
            'feed' => $feed
        ));
    }



    public function DeletCommentsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $Pro = $em->getRepository('EventsBundle:Feed')->find($id);
        $em->remove($Pro);
        $em->flush();
        return $this->redirectToRoute('Feed_Index');
    }


    private function createDeleteForm(Events $evenement)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('evenement_delete', array('ref' => $evenement->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

}