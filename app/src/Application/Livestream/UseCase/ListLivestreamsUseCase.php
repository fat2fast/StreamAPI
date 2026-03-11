<?php

namespace Application\Livestream\UseCase;

use Application\Livestream\DTO\ListLivestreamsInput;
use Application\Livestream\DTO\ListLivestreamsOutput;
use Application\Livestream\DTO\LivestreamOutput;
use Domain\Livestream\Entity\Livestream;
use Domain\Livestream\Repository\LivestreamRepositoryInterface;

final readonly class ListLivestreamsUseCase
{
    public function __construct(private LivestreamRepositoryInterface $livestreamRepository)
    {
    }

    public function __invoke(ListLivestreamsInput $_input): ListLivestreamsOutput
    {
        $items = $this->livestreamRepository->listActive();

        $outputs = array_map(
            static fn (Livestream $livestream): LivestreamOutput => LivestreamOutput::fromEntity($livestream),
            $items
        );

        return new ListLivestreamsOutput(
            items: $outputs,
            total: count($outputs)
        );
    }
}
