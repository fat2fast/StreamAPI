<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Livestream\UseCase;

use Application\Livestream\DTO\StartRoomInput;
use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\UseCase\StartRoomUseCase;
use DateTimeImmutable;
use Domain\Livestream\Entity\Livestream;
use Domain\Livestream\Enum\LivestreamStatus;
use Domain\Livestream\Repository\LivestreamRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class StartRoomUseCaseTest extends TestCase
{
    public function testStartRoomCreatesActiveLivestream(): void
    {
        $repository = new InMemoryLivestreamRepository();
        $useCase = new StartRoomUseCase($repository);

        $output = $useCase(new StartRoomInput(streamerId: 10, title: 'Refactoring session'));

        self::assertGreaterThan(0, $output->id);
        self::assertSame(10, $output->streamerId);
        self::assertSame('Refactoring session', $output->title);
        self::assertSame('active', $output->status);
        self::assertNull($output->closedAt);
    }

    public function testStartRoomThrowsConflictWhenStreamerAlreadyHasActiveRoom(): void
    {
        $repository = new InMemoryLivestreamRepository();
        $repository->save(new Livestream(
            id: 0,
            streamerId: 7,
            title: 'Existing room',
            status: LivestreamStatus::Active,
            startedAt: new DateTimeImmutable('now'),
            closedAt: null
        ));

        $useCase = new StartRoomUseCase($repository);

        $this->expectException(ActiveLivestreamConflictException::class);

        $useCase(new StartRoomInput(streamerId: 7, title: 'Second room'));
    }

    public function testStartRoomRejectsInvalidTitle(): void
    {
        $repository = new InMemoryLivestreamRepository();
        $useCase = new StartRoomUseCase($repository);

        $this->expectException(InvalidLivestreamInputException::class);

        $useCase(new StartRoomInput(streamerId: 12, title: '   '));
    }
}

final class InMemoryLivestreamRepository implements LivestreamRepositoryInterface
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
