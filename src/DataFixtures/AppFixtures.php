<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $priorityArray = ["Haute", "Moyenne", "Basse", "Optionnel"];

        $faker = Factory::create("fr_FR");
        for ($i=0; $i < 20; $i++) { 
            $priority = $priorityArray[mt_rand(0,3)];
            $task = new Task();
        
            $task->setTitle($faker->sentence);
            $task->setDescription($faker->text);
            $task->setPriority($priority);
            $task->setCreationDate($faker->date);
            $task->setDueDate($faker->date);

            $manager->persist($task);
        }
       

        $manager->flush();
    }
}
