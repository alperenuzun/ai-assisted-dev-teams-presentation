<?php

declare(strict_types=1);

use App\Api\Domain\Post\Entity\Post;
use App\Api\Domain\Post\ValueObject\PostContent;
use App\Api\Domain\Post\ValueObject\PostTitle;
use App\SharedKernel\Domain\Exception\DomainException;
use App\SharedKernel\Domain\ValueObject\Uuid;

test('can create a post', function () {
    $post = Post::create(
        Uuid::generate(),
        PostTitle::fromString('Test Title'),
        PostContent::fromString('Test Content for the blog post'),
        Uuid::generate()
    );

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->getStatus()->isDraft())->toBeTrue();
});

test('can publish a draft post', function () {
    $post = Post::create(
        Uuid::generate(),
        PostTitle::fromString('Test Title'),
        PostContent::fromString('Test Content for the blog post'),
        Uuid::generate()
    );

    $post->publish();

    expect($post->getStatus()->isPublished())->toBeTrue()
        ->and($post->getPublishedAt())->not->toBeNull();
});

test('cannot publish an already published post', function () {
    $post = Post::create(
        Uuid::generate(),
        PostTitle::fromString('Test Title'),
        PostContent::fromString('Test Content for the blog post'),
        Uuid::generate()
    );

    $post->publish();

    expect(fn() => $post->publish())
        ->toThrow(DomainException::class, 'Post is already published');
});

test('can archive a published post', function () {
    $post = Post::create(
        Uuid::generate(),
        PostTitle::fromString('Test Title'),
        PostContent::fromString('Test Content for the blog post'),
        Uuid::generate()
    );

    $post->publish();
    $post->archive();

    expect($post->getStatus()->isArchived())->toBeTrue();
});
