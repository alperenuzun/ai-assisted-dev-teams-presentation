<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Fixtures;

use App\Api\Domain\User\Entity\User;
use App\Api\Domain\User\ValueObject\UserRole;
use App\SharedKernel\Domain\ValueObject\Email;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const REGULAR_USER_REFERENCE = 'regular-user';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $adminUser = User::create(
            Uuid::fromString('550e8400-e29b-41d4-a716-446655440000'),
            Email::fromString('admin@blog.com'),
            '',
            UserRole::admin()
        );
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'password');
        $adminUser->changePassword($hashedPassword);
        $manager->persist($adminUser);
        $this->addReference(self::ADMIN_USER_REFERENCE, $adminUser);

        // Create regular user
        $regularUser = User::create(
            Uuid::fromString('550e8400-e29b-41d4-a716-446655440001'),
            Email::fromString('user@blog.com'),
            '',
            UserRole::user()
        );
        $hashedPassword = $this->passwordHasher->hashPassword($regularUser, 'password');
        $regularUser->changePassword($hashedPassword);
        $manager->persist($regularUser);
        $this->addReference(self::REGULAR_USER_REFERENCE, $regularUser);

        $manager->flush();
    }
}
