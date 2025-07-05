<?php

declare(strict_types=1);

namespace App\Dto;

class ResponseMetaDto {
    public function __construct(
        public string $code,
        public int $duration,
        public string $message,
        public ?int $entries_from = null,
        public ?int $entries_to = null,
        public ?int $entries_total = null,
        public ?int $entries_sum = null
    ) {
    }

}
