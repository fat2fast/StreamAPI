<?php

namespace Application\Livestream\UseCase;

use Application\Livestream\DTO\LivestreamOutput;
use Application\Livestream\DTO\StartRoomInput;
use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Domain\Livestream\Entity\Livestream;
use Domain\Livestream\Enum\LivestreamStatus;
use Domain\Livestream\Repository\LivestreamRepositoryInterface;

final readonly class StartRoomUseCase
{
    public function __construct(private LivestreamRepositoryInterface $livestreamRepository)
    {
    }

    public function __invoke(StartRoomInput $input): LivestreamOutput
    {
        if ($input->streamerId <= 0) {
            throw new InvalidLivestreamInputException('Streamer id must be greater than zero');
        }

        $title = trim($input->title);
        if ($title === '' || mb_strlen($title) > 255) {
            throw new InvalidLivestreamInputException('Title is required and must be <= 255 characters');
        }

        $active = $this->livestreamRepository->findActiveByStreamerId($input->streamerId);
        if ($active !== null) {
            throw new ActiveLivestreamConflictException();
        }

        $livestream = new Livestream(
            id: 0,
            streamerId: $input->streamerId,
            title: $title,
            status: LivestreamStatus::Active,
            startedAt: new \DateTimeImmutable('now'),
            closedAt: null
        );

        $created = $this->livestreamRepository->save($livestream);

        return LivestreamOutput::fromEntity($created);
    }
}
