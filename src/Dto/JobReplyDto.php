<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class JobReplyDto {
    public function __construct(
        #[Assert\NotBlank(message: 'Jméno a příjmení nesmí být prázdné.')]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email(message: 'Zadejte prosím platnou e-mailovou adresu.')]
        public string $email,

        #[Assert\NotBlank]
        public string $phone,

        #[Assert\NotBlank]
        public string $message,
    ) {
    }
}
