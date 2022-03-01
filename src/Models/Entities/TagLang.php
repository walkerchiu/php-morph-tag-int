<?php

namespace WalkerChiu\MorphTag\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class TagLang extends Lang
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.morph-tag.tags_lang');

        parent::__construct($attributes);
    }
}
