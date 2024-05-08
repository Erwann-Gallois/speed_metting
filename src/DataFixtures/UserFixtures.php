<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        //Creation Professionnel
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setNom($faker->lastName());
            $user->setPrenom($faker->firstName());
            $user->setEmail($faker->email());
            $password = $this->hasher->hashPassword($user, '1234');
            $user->setPassword($password);
            $user->setRoles(['ROLE_PROFESSIONNEL']);
            $user->setType(1);
            $user->setEntreprise($faker->company());
            $user->setEtude($faker->text());
            $user->setPoste($faker->text());
            $user->setQuestion($faker->text());
            $manager->persist($user);
        }

        //Creation Eleve
        for ($i = 0; $i < 60; $i++) {
            $user = new User();
            $user->setNom($faker->lastName);
            $user->setPrenom($faker->firstName);
            $user->setEmail($faker->email);
            $password = $this->hasher->hashPassword($user, '1234');
            $user->setPassword($password);
            $user->setRoles(['ROLE_ELEVE']);
            $user->setType(2);
            $user->setSession($rand = rand(1, 2));
            $user->setFiliere($faker->text());
            $user->setQuestion($faker->text());
            $manager->persist($user);
        }

        $manager->flush();
    }
}