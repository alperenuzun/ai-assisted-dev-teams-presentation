<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Persistence\Fixtures;

use App\Api\Domain\Post\Entity\Post;
use App\Api\Domain\Post\ValueObject\PostContent;
use App\Api\Domain\Post\ValueObject\PostTitle;
use App\SharedKernel\Domain\ValueObject\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var \App\Api\Domain\User\Entity\User $adminUser */
        $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE);

        // Create published post
        $post1 = Post::create(
            Uuid::generate(),
            PostTitle::fromString('Welcome to Our Blog'),
            PostContent::fromString('This is the first post on our blog. We are excited to share our thoughts and ideas with you!'),
            $adminUser->getId()
        );
        $post1->publish();
        $manager->persist($post1);

        // Create draft post
        $post2 = Post::create(
            Uuid::generate(),
            PostTitle::fromString('Domain-Driven Design with Symfony'),
            PostContent::fromString('In this post, we will explore how to implement DDD principles using Symfony framework...'),
            $adminUser->getId()
        );
        $manager->persist($post2);

        // Create another published post
        $post3 = Post::create(
            Uuid::generate(),
            PostTitle::fromString('CQRS Pattern in Practice'),
            PostContent::fromString('Command Query Responsibility Segregation is a powerful pattern for complex applications...'),
            $adminUser->getId()
        );
        $post3->publish();
        $manager->persist($post3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
