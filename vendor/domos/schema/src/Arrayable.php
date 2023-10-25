<?php

namespace SchemaImmo;

interface Arrayable
{
    public static function from(array $data): self;
    public function toArray(): array;
}
