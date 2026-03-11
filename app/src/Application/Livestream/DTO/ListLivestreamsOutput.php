<?php

namespace Application\Livestream\DTO;

readonly class ListLivestreamsOutput
{
    /**
     * @param list<LivestreamOutput> $items
     */
    public function __construct(
        public array $items,
        public int $total
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (LivestreamOutput $item): array => $item->toArray(),
                $this->items
            ),
            'total' => $this->total,
        ];
    }
}
