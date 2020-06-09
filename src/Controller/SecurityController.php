<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Compte;
use App\Form\RegistrationType;
use App\Form\AdminRegistrationType;
use App\Form\ClientRegistrationType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security_login")
     */
    public function login()
    {
        return $this->render('security/login.html.twig');
    }
    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        return $this->render('security/login.html.twig');
    }
    /**
     * @Route("/registred", name="registred")
     */
    public function Done()
    {
        return $this->render('security/registred.html.twig');
    }
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request,ManagerRegistry $managerRegistry,UserPasswordEncoderInterface $encoder)
    {
        $compte = new Compte();
        $client = new Client(); 
        $x = 0;
       
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
            return $this->redirectToRoute('registred');
        }
        return $this->render('security/registration.html.twig',[
            'formCompte' => $formCompte->createView(),
            'formClient' => $formClient->createView(),
            'createdId' => $client->getId(),
            'ok' => $client->getFileName(),
            'ok1' => $x

        ]);
    }
    /**
     * @Route("/admin-reg", name="admin_registration")
     */
    public function registration_admin(Request $request,ManagerRegistry $managerRegistry,UserPasswordEncoderInterface $encoder)
    {
        $compte = new Compte();
        $admin = new Admin(); 
   
        $formAdmin = $this->createForm(AdminRegistrationType::class,$admin);
        $formCompte = $this->createForm(RegistrationType::class,$compte);
        $manager = $managerRegistry->getManager();

        $formAdmin->handleRequest($request);
        $formCompte->handleRequest($request); 

        if($formAdmin->isSubmitted() && $formAdmin->isValid()){
            
            $manager->persist($admin);
            $manager->flush();
   
            if($formCompte->isSubmitted() && $formCompte->isValid())
            {
                $compte->setType('admin');
                if($compte->getType() === 'admin'){
                    $compte->setIdAdmin($admin);
                }
                $hash = $encoder->encodePassword($compte,$compte->getPassword());
                $compte->setPassword($hash);
                $manager->persist($compte);
                $manager->flush();
            }
          
            
            return $this->redirectToRoute('security_login');
        }
        return $this->render('admin/adminReg.html.twig',[
            'formCompte' => $formCompte->createView(),
            'formAdmin' => $formAdmin->createView()

        ]);
    }
}
