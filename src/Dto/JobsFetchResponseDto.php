<?php

declare(strict_types=1);

namespace App\Dto;

class JobsFetchResponseDto {
    /**
     * @param JobDto[] $payload
     * @param ResponseMetaDto $meta
     */
    public function __construct(
        public array $payload,
        public ResponseMetaDto $meta,
    ) {}
}
