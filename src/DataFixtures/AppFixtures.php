<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

# Source: https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html
# Source: https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html#accessing-services-from-the-fixtures
class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Admin');
        $user->setEmail('admin@admin.com');
        $user->setPassword($this->hasher->hashPassword($user, 'adminadmin'));
        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);
        $manager->flush();
    }
}
