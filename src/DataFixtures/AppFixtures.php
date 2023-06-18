<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    protected $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        $admin = new User;

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password');
        $admin->setEmail("admin@gmail.com")
            ->setPassword($hashedPassword)
            ->setFullName("Admin")
            ->setRoles(["ROLE_ADMIN"]);

        $manager->persist($admin);
            
        for ($j=0; $j < 5; $j++) { 
            $user = new User();

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setEmail("user$j@gmail.com")
                ->setFullName($faker->name())
                ->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $priorityArray = ["Haute", "Moyenne", "Basse", "Optionnel"];

        for ($i=0; $i < 20; $i++) { 
            $priority = $priorityArray[mt_rand(0,3)];
            $task = new Task();
        
            $task->setTitle($faker->sentence);
            $task->setDescription($faker->text);
            $task->setPriority($priority);
            $task->setCreationDate($faker->dateTime('Y-m-d'));
            $task->setDueDate($faker->dateTime('Y-m-d'));

            $manager->persist($task);
        }

        
       

        $manager->flush();
    }
}
