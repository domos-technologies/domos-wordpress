<?php

namespace SchemaImmo\WebExpose\Block;

use SchemaImmo\CanHaveExtraData;
use SchemaImmo\WebExpose\Block;
use SchemaImmo\WebExpose\BlockType;

class CustomBlock extends Block
{
    /** @var string|null */
    public $html;

    public function __construct(
        array $data = [],

        /** @var string|null */
        $id = null,

        /** @var string|null */
        $html = null,
    )
    {
        parent::__construct(
            BlockType::Custom,
            $id,
        );

        $this->html = $html;
        $this->extra = $data;
    }

    public function fill(array $data): static
    {
        parent::fill($data);

        $this->html = $data['html'] ?? null;

        return $this;
    }

    public function toArrayWithoutExtra(): array
    {
        return [
            'html' => $this->html,
        ];
    }
}
