<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Ticket;
use App\Entity\Client;
use App\Entity\Technicien;


class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('en_US');

        for($i =1 ;$i <= 3 ;$i++)
        {  
            $client = new Client();
            $client->setNom($faker->firstName())
                    ->setPrenom($faker->lastName())
                    ->setTelephone($faker->randomNumber())
                    ->setAdresse($faker->address());
            $manager->persist($client);
            $tech = new Technicien();
            $tech->setNom($faker->firstName())
                    ->setPrenom($faker->lastName())
                    ->setTelephone($faker->randomNumber());
            $manager->persist($tech);
            for($j =1 ;$j <= 5 ;$j++){
                $ticket = new Ticket();
                $ticket->setTitle($faker->sentence())
                        ->setCategorie($faker->sentence(2))
                        ->setDescription($faker->paragraph())
                        ->setEtatTicket($faker->word())
                        ->setClient($client)
                        ->setTechnicien($tech)
                        ->setCreatedAt($faker->dateTimeBetween('-6 months'));
                $manager->persist($ticket);
            }
        }
        
        $manager->flush();
    }
}
