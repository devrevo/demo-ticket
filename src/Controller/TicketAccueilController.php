<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Ticket;
use App\Entity\Category;
use App\Entity\Technicien;
use App\Form\ClientRegistrationType;
use App\Repository\TicketRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TicketAccueilController extends AbstractController
{

    /**
     * @Route("/", name="ticket_accueil")
     * @Route("/administration/{id}", name="admin_home")
     * @Route("/technicien/{id}",name="tech_home")
     */
    public function index(Ticket $ticket = null,Request $request,ManagerRegistry $managerRegistry)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $repository = $this->getDoctrine()->getRepository(Compte::class);
            $compte = $repository->findOneById($this->getUser()->getId());
            if ($compte->getType() === 'client')
            {
                $repository = $this->getDoctrine()->getRepository(Client::class);
                $client = $repository->findOneById($compte->getIdClient());
                return $this->render('ticket_accueil/index.html.twig',[
                    'client' => $client,
                    'compte' => $compte
                ]);
            }else if ($compte->getType() === 'admin')
            {
                $repository = $this->getDoctrine()->getRepository(Admin::class);
                $admin = $repository->findOneById($compte->getIdAdmin());
                $repository = $this->getDoctrine()->getRepository(Ticket::class); 
                $tickets = $repository->findByTechnicien(null);
                $repository = $this->getDoctrine()->getRepository(Technicien::class);
                $techs = $repository->findAll();
                
                if ($request->getMethod() == Request::METHOD_POST){
                    if(!$ticket)
                    {
                        $ticket = new Ticket();
                    }
                    $v = $request->request->get('techs');
                    $tech = $repository->findOneById($v);
                    $ticket->setTechnicien($tech);
                    $manager = $managerRegistry->getManager();
                    $manager->persist($ticket);
                    $manager->flush();
                    return $this->redirect($request->getUri());
                }
                return $this->render('admin/admin.html.twig',[
                    'admin' => $admin,
                    'tickets' => $tickets,
                    'techs' => $techs
                ]);
            } else
            {
                $repository = $this->getDoctrine()->getRepository(Technicien::class);
                $tech = $repository->findOneById($compte->getIdTech());
                $repository = $this->getDoctrine()->getRepository(Ticket::class); 
                $tickets = $repository->findByTechEtat($tech);
                $repository = $this->getDoctrine()->getRepository(Etat::class);
                $etats = $repository->findAll();
                $url = $request->getUri() ;
                if ($request->getMethod() == Request::METHOD_POST){
                    if(!$ticket)
                    {
                        $ticket = new Ticket();
                    }
                    
                    $v = $request->request->get('etats');
                    $ticket->setEtatTicket($v);
                    $manager = $managerRegistry->getManager();
                    $manager->persist($ticket);
                    $manager->flush();
                    return $this->redirect($url);
                }
                return $this->render('Tech/technicien.html.twig',[
                    'tech' => $tech,
                    'tickets' => $tickets,
                    'etats' => $etats
                ]);
            }      
        }
        
    }
     /**
     * @Route("/createticket", name="cticket")
     * @Route("/all-tickets/{id}/edit", name="eticket")
     */
    public function ticket_page(Ticket $ticket = null ,Request $request,ManagerRegistry $managerRegistry)
    {
        if(!$ticket){
            $ticket = new Ticket();
        }
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');
        }else
        {
            $repository = $this->getDoctrine()->getRepository(Compte::class);
            $compte = $repository->findOneById($this->getUser()->getId());
            $repository = $this->getDoctrine()->getRepository(Client::class);
            $client = $repository->findOneById($compte->getIdClient());

            $form = $this->createFormBuilder($ticket)
                     ->add('title',null,[
                         'attr' => [
                             'placeholder' => "Titre",
                             'class' => 'form-control'
                         ]
                     ])
                     ->add('Categorie' , EntityType::class ,[
                                    'class'    => Category::class,
                                    'choice_label'  => 'titre',
                                    'attr' => [
                                        'placeholder' => "Titre",
                                        'class' => 'form-control'
                                        ]])
                     ->add('description',TextareaType::class,[
                        'attr' => [
                            'placeholder' => "Description",
                            'class' => 'form-control'
                        ]
                    ])
                     ->getForm();
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                if(!$ticket){
                    $ticket = new Ticket();
                }
                
                $ticket->setEtatTicket('En cours')
                        ->setClient($client)
                        ->setCreatedAt(new \DateTime());
                $manager = $managerRegistry->getManager();
                $manager->persist($ticket);
                $manager->flush();
                return $this->redirectToRoute('tickets');
            }
            return $this->render('ticket_accueil/newticket.html.twig',[
                'FormTicket' => $form->createView(),
                'EditMode'   => $ticket->getId() !== null,
                'user' => $client->getId()
            ]);
            
        }
        
    }
    /**
     * @Route("/all-tickets", name="tickets")
     */
    public function Tickets_page(TicketRepository $repo)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $repository = $this->getDoctrine()->getRepository(Compte::class);
            $compte = $repository->findOneById($this->getUser()->getId());
            $repository = $this->getDoctrine()->getRepository(Client::class);
            $client = $repository->findOneById($compte->getIdClient());
            $tickets = $repo->findByClient($compte->getIdClient());
            return $this->render('ticket_accueil/alltickets.html.twig',[
                'tickets' => $tickets
            ]);
        }
    }
     /**
     * @Route("/all-tickets/{id}", name="ticket_show")
     */
    public function t_page(Ticket $ticket)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            return $this->render('ticket_accueil/ticket.html.twig',[
                'ticket' => $ticket
            ]);
        }
    }

    
    /**
     * @Route("/edit-Profile", name="edit_profile")
     */
    public function edit_profile(Request $request, ManagerRegistry $managerRegistry)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $repository = $this->getDoctrine()->getRepository(Compte::class);
            $c = $repository->findOneById($this->getUser()->getId());
            $repository = $this->getDoctrine()->getRepository(Client::class);
            $cl = $repository->findOneById($c->getIdClient());
            $compte = new Compte();
            $client = new Client();
            $formClient = $this->createForm(ClientRegistrationType::class,$client);
            $manager = $managerRegistry->getManager();
            $d = '';
            $formClient->handleRequest($request);
            if($formClient->isSubmitted() && $formClient->isValid() )
            {
                //(new UploadedFile(,$client->getImageFile()));
                $cl->setFileName($client->getImageFile()->getClientOriginalName());
                $d = $client->getImageFile()->getClientOriginalName();
                if($client->getFileName() !== null)
                {
                    $cl->setFileName($client->getFileName());
                }
                if (!$cl) {
                    throw $this->createNotFoundException(
                        'No client found '
                    );
                }
                $cl->setNom($formClient['Nom']->getData())
                    ->setPrenom($formClient['Prenom']->getData())
                    ->setTelephone($formClient['Telephone']->getData())
                    ->setAdresse($formClient['Adresse']->getData());
             
                $manager->persist($cl);
                $manager->flush();

                //return $this->redirectToRoute('ticket_accueil');
            }
            return $this->render('ticket_accueil/editprofile.html.twig',[
                'FormClient' => $formClient->createView(),
                'id' => $c,
                'd' => $d

            ]);
        }
    }
    /**
     * @Route("/vticket", name="vticket")
     */
    public function F_page(TicketRepository $repo)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $repository = $this->getDoctrine()->getRepository(Compte::class);
            $compte = $repository->findOneById($this->getUser()->getId());
            $repository = $this->getDoctrine()->getRepository(Client::class);
            $client = $repository->findOneById($compte->getIdClient());
            $tickets = $repo->findByClient($compte->getIdClient());
            return $this->render('ticket_accueil/vticket.html.twig',[
                'tickets' => $tickets
            ]);
        }
    }
    
}
