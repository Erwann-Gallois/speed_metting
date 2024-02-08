<?php

namespace App\DataFixtures;

use App\Entity\Session;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SessionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $pro = $manager->getRepository(User::class)->findBy(['type' => 1]);
        $eleve = $manager->getRepository(User::class)->findBy(['type' => 2]);
        $heures = [
            '14:00',
            '14:10',
            '14:20',
            '14:30',
        ];
        //Creation Session
        foreach ($heures as $heure) {
            for ($i = 0; $i < 5; $i++) {
                $session = new Session();
                $session->setPro($manager->getRepository(User::class)->findBy(['id' => 1])[0]);
                $session->setEleve($faker->randomElement($eleve));
                $session->setHeure(new \DateTime($heure));
                $manager->persist($session);
            }
        }
        $manager->flush();
    }
}
