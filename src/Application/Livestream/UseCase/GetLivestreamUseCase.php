<?php

namespace Application\Livestream\UseCase;

use Application\Livestream\DTO\GetLivestreamInput;
use Application\Livestream\DTO\LivestreamOutput;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\Exception\LivestreamNotFoundException;
use Domain\Livestream\Repository\LivestreamRepositoryInterface;

final readonly class GetLivestreamUseCase
{
    public function __construct(private LivestreamRepositoryInterface $livestreamRepository)
    {
    }

    public function __invoke(GetLivestreamInput $input): LivestreamOutput
    {
        if ($input->livestreamId <= 0) {
            throw new InvalidLivestreamInputException('Livestream id must be greater than zero');
        }

        $livestream = $this->livestreamRepository->findActiveById($input->livestreamId);

        if ($livestream === null) {
            throw new LivestreamNotFoundException($input->livestreamId);
        }

        return LivestreamOutput::fromEntity($livestream);
    }
}
