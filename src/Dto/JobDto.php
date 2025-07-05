<?php

declare(strict_types=1);

namespace App\Dto;

class JobDto {
    /**
     * @param int $job_id
     * @param string $title
     * @param string $description
     * @param array $employment
     * @param array $addresses
     */
    public function __construct(
        public int $job_id,
        public string $title,
        public string $description,
        public array $employment,
        public array $addresses,
    ) {}
}
