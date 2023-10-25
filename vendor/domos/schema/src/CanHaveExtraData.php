<?php

namespace SchemaImmo;

trait CanHaveExtraData
{
	protected array $extra = [];

	abstract public function fill(array $data): static;

	abstract public function toArrayWithoutExtra(): array;
}
