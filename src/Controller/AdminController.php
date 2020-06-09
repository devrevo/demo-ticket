<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Ticket;
use App\Entity\Technicien;
use App\Form\RegistrationType;
use App\Form\TechRegistrationType;
use App\Form\ClientRegistrationType;
use App\Repository\ClientRepository;
use App\Repository\TicketRepository;
use App\Repository\TechnicienRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/techniciens", name="page_techniciens")
     */
    public function page_techs(TechnicienRepository $repo)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $techs = $repo->findAll();
            return $this->render('admin/techniciens.html.twig',[
                'techs' => $techs
            ]);
        }
    }
     /**
     * @Route("/add-client", name="page_addclients")
     */
    public function page_add_clients(TechnicienRepository $repo,Request $request,ManagerRegistry $managerRegistry,UserPasswordEncoderInterface $encoder)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
           
        $compte = new Compte();
        $client = new Client(); 
      
        $formClient = $this->createForm(ClientRegistrationType::class,$client);
        $formCompte = $this->createForm(RegistrationType::class,$compte);
        $manager = $managerRegistry->getManager();

        $formClient->handleRequest($request);
        $formCompte->handleRequest($request); 

        if($formClient->isSubmitted() && $formClient->isValid()){
            if($client->getFileName() !== null)
            {
                $manager->persist($client);
                $manager->flush();
            }else
            {
                $client->setFileName('index.png')
                       ->setUpdatedAt(new \DateTimeImmutable());
            }
            

            if($formCompte->isSubmitted() && $formCompte->isValid())
            {
                $compte->setType('client');
                if($compte->getType() === 'client'){
                    $compte->setIdClient($client);
                }
                $hash = $encoder->encodePassword($compte,$compte->getPassword());
                $compte->setPassword($hash);
                $manager->persist($compte);
                $manager->flush();
            }
          
            
            //return $this->redirectToRoute('security_login');
        }
        return $this->render('admin/addclient.html.twig',[
            'formCompte' => $formCompte->createView(),
            'formClient' => $formClient->createView()
        ]);
        }
    }
    /**
     * @Route("/clients", name="page_clients")
     */
    public function page_clients(ClientRepository $repo)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $clients = $repo->findAll();
            return $this->render('admin/clients.html.twig',[
                'clients' => $clients
            ]);
        }
    }
     /**
     * @Route("//techniciens/{id}", name="techinf")
     */
    public function page_techinf(Technicien $tech = null)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            if(!$tech){
                $tech = new Technicien();
            }
            $repository = $this->getDoctrine()->getRepository(Technicien::class);
            $technicien = $repository->findOneById($tech); 
            return $this->render('admin/techinf.html.twig',[
                'tech' => $technicien
            ]);
        }
    }
    /**
     * @Route("/tickets", name="page_tickets")
     */
    public function page_tickets(TicketRepository $repo,Request $request)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $repository = $this->getDoctrine()->getRepository(Etat::class);
            $Etat = $repository->findAll();
            $tickets = $repo->findAll();
            $v = "Etat";
            if ($request->getMethod() == Request::METHOD_POST){
                $v = $request->request->get('mySelect');
                
                $tickets = $repo->findByEtatTicket($v);

            }
            return $this->render('admin/tickets.html.twig',[
                'tickets' => $tickets,
                'etats' => $Etat,
                'v' => $v
                ]);
        }
    }
    /**
     * @Route("/technicien-add", name="page_technicienadd")
     */
    public function page_techAdd(TechnicienRepository $repo ,Request $request ,ManagerRegistry $managerRegistry,UserPasswordEncoderInterface $encoder)
    {
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');

        }else
        {
            $tech = new Technicien(); 
            $compte = new Compte();
            $x = 0;
       
            $formTech = $this->createForm(TechRegistrationType::class,$tech);
            $formCompte = $this->createForm(RegistrationType::class,$compte);
            $manager = $managerRegistry->getManager();

       
            $formTech->handleRequest($request); 
            $formCompte->handleRequest($request); 
            if($formTech->isSubmitted() && $formTech->isValid())
            {
                
                    $manager->persist($tech);
                    $manager->flush();  

                if($formCompte->isSubmitted() && $formCompte->isValid())
                {
                    $compte->setType('tech');
                    if($compte->getType() === 'tech'){
                        $compte->setIdTech($tech);
                    }
                    $hash = $encoder->encodePassword($compte,$compte->getPassword());
                    $compte->setPassword($hash);
                    $manager->persist($compte);
                    $manager->flush();
                }
            //return $this->redirectToRoute('security_login');
            }
            return $this->render('admin/techregistration.html.twig',[
                'formCompte' => $formCompte->createView(),
                'formTech' => $formTech->createView()

            ]);
            
        }
    }
    /**
     * @Route("/createticket", name="cticket")
     * @Route("/tickets/{id}", name="ticketsClient")
     */
    public function client_page(Client $client = null ,Request $request,ManagerRegistry $managerRegistry)
    {
        if(!$client){

            $client= new Client();
        }        
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');
        }else
        {
            $repository = $this->getDoctrine()->getRepository(Compte::class);
            $compte = $repository->findOneById($this->getUser()->getId());
            $repository = $this->getDoctrine()->getRepository(Admin::class);
            $admin = $repository->findOneById($compte->getIdAdmin());
            $repository = $this->getDoctrine()->getRepository(Ticket::class);
            $tickets = $repository->findByClient($client);
            return $this->render('admin/ticketclient.html.twig',[
                'tickets' => $tickets
            ]);
            
        }
        
    }

    /**
     * 
     * @Route("/tickets/ticket/{id}", name="ticketinf")
     */
    public function ticket_page(Ticket $ticket = null ,Request $request,ManagerRegistry $managerRegistry)
    {
        if(!$ticket){

            $ticket= new Ticket();
        }        
        if($this->getUser()== null){
            
            return $this->render('security/login.html.twig');
        }else
        {
            $repository = $this->getDoctrine()->getRepository(Compte::class);
            $compte = $repository->findOneById($this->getUser()->getId());
            $repository = $this->getDoctrine()->getRepository(Admin::class);
            $admin = $repository->findOneById($compte->getIdAdmin());

            return $this->render('admin/ticketinf.html.twig',[
                'ticket' => $ticket
            ]);
            
        }
        
    }
}
