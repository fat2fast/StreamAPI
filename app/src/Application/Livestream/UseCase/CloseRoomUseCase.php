<?php

namespace Application\Livestream\UseCase;

use Application\Livestream\DTO\CloseRoomInput;
use Application\Livestream\DTO\LivestreamOutput;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Domain\Livestream\Repository\LivestreamRepositoryInterface;

final readonly class CloseRoomUseCase
{
    public function __construct(private LivestreamRepositoryInterface $livestreamRepository)
    {
    }

    public function __invoke(CloseRoomInput $input): LivestreamOutput
    {
        if ($input->streamerId <= 0) {
            throw new InvalidLivestreamInputException('Streamer id must be greater than zero');
        }

        $active = $this->livestreamRepository->findActiveByStreamerId($input->streamerId);
        if ($active === null) {
            throw new ActiveLivestreamNotFoundException();
        }

        $closed = $active->close(new \DateTimeImmutable('now'));
        $saved = $this->livestreamRepository->save($closed);

        return LivestreamOutput::fromEntity($saved);
    }
}
