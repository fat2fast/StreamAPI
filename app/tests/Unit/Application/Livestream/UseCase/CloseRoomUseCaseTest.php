<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Livestream\UseCase;

use Application\Livestream\DTO\CloseRoomInput;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\UseCase\CloseRoomUseCase;
use DateTimeImmutable;
use Domain\Livestream\Entity\Livestream;
use Domain\Livestream\Enum\LivestreamStatus;
use Domain\Livestream\Repository\LivestreamRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CloseRoomUseCaseTest extends TestCase
{
    public function testCloseRoomMarksActiveRoomAsClosed(): void
    {
        $repository = new InMemoryCloseLivestreamRepository();
        $repository->save(new Livestream(
            id: 0,
            streamerId: 22,
            title: 'Morning stream',
            status: LivestreamStatus::Active,
            startedAt: new DateTimeImmutable('2026-03-11T08:00:00+00:00'),
            closedAt: null
        ));

        $useCase = new CloseRoomUseCase($repository);
        $output = $useCase(new CloseRoomInput(streamerId: 22));

        self::assertSame('closed', $output->status);
        self::assertNotNull($output->closedAt);
    }

    public function testCloseRoomThrowsNotFoundWhenNoActiveRoomExists(): void
    {
        $repository = new InMemoryCloseLivestreamRepository();
        $useCase = new CloseRoomUseCase($repository);

        $this->expectException(ActiveLivestreamNotFoundException::class);

        $useCase(new CloseRoomInput(streamerId: 77));
    }

    public function testCloseRoomRejectsInvalidStreamerId(): void
    {
        $repository = new InMemoryCloseLivestreamRepository();
        $useCase = new CloseRoomUseCase($repository);

        $this->expectException(InvalidLivestreamInputException::class);

        $useCase(new CloseRoomInput(streamerId: 0));
    }
}

final class InMemoryCloseLivestreamRepository implements LivestreamRepositoryInterface
{
    /** @var array<int, Livestream> */
    private array $items = [];
    private int $autoIncrement = 1;

    public function findActiveByStreamerId(int $streamerId): ?Livestream
    {
        foreach ($this->items as $item) {
            if ($item->streamerId === $streamerId && $item->isActive()) {
                return $item;
            }
        }

        return null;
    }

    public function findActiveById(int $livestreamId): ?Livestream
    {
        $item = $this->items[$livestreamId] ?? null;

        return $item !== null && $item->isActive() ? $item : null;
    }

    public function listActive(): array
    {
        return array_values(
            array_filter(
                $this->items,
                static fn (Livestream $livestream): bool => $livestream->isActive()
            )
        );
    }

    public function save(Livestream $livestream): Livestream
    {
        if ($livestream->id <= 0) {
            $livestream = new Livestream(
                id: $this->autoIncrement++,
                streamerId: $livestream->streamerId,
                title: $livestream->title,
                status: $livestream->status,
                startedAt: $livestream->startedAt,
                closedAt: $livestream->closedAt
            );
        }

        $this->items[$livestream->id] = $livestream;

        return $livestream;
    }
}
