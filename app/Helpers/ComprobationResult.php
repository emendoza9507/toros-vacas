<?php

namespace App\Helpers;

class ComprobationResult extends Response
{

    public function __construct(
        public string $combination,
        public string $combination_result,
        public mixed $result,
        public string $attempts,
        public $evaluation,
        public $ranking
    )
    {}
}