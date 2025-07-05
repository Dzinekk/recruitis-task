<?php

declare(strict_types=1);

namespace App\Dto;

class JobFetchResponseDto {
    public function __construct(
        public JobDto $payload,
        public ResponseMetaDto $meta,
    ) {
    }
}
