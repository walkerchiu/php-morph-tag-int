<?php

namespace WalkerChiu\MorphTag\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;
use WalkerChiu\Core\Models\Entities\LangTrait;

class Tag extends Entity
{
    use LangTrait;



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.morph-tag.tags');

        $this->fillable = array_merge($this->fillable, [
            'host_type', 'host_id',
            'serial',
            'identifier',
            'order'
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get it's lang entity.
     *
     * @return Lang
     */
    public function lang()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-morph-tag.onoff.core-lang_core')
        ) {
            return config('wk-core.class.core.langCore');
        } else {
            return config('wk-core.class.morph-tag.tagLang');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function langs()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-morph-tag.onoff.core-lang_core')
        ) {
            return $this->langsCore();
        } else {
            return $this->hasMany(config('wk-core.class.morph-tag.tagLang'), 'morph_id', 'id');
        }
    }

    /**
     * Get the owning host model.
     */
    public function host()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function sites()
    {
        return $this->morphedByMany(config('wk-core.class.site.site'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * @param String  $type
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function categories($type = null)
    {
        return $this->morphedByMany(config('wk-core.class.morph-category.category'), 'morph', config('wk-core.table.morph-tag.tags_morphs'))
                    ->when($type, function ($query, $type) {
                                return $query->where( function ($query) use ($type) {
                                    return $query->whereNull('type')
                                                ->orWhere('type', $type);
                                });
                            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function catalogs()
    {
        return $this->morphedByMany(config('wk-core.class.mall-shelf.catalog'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function devices()
    {
        return $this->morphedByMany(config('wk-core.class.device.device'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function devicesSensor()
    {
        return $this->morphedByMany(config('wk-core.class.device-sensor.device'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function devicesModbus()
    {
        return $this->morphedByMany(config('wk-core.class.device-modbus.main'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * @param String  $type
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function devicesRfid($type)
    {
        if ($type == 'reader')
            return $this->morphedByMany(config('wk-core.class.device-rfid.reader'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
        elseif ($type == 'card')
            return $this->morphedByMany(config('wk-core.class.device-rfid.card'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function products()
    {
        return $this->morphedByMany(config('wk-core.class.mall-shelf.product'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function stocks()
    {
        return $this->morphedByMany(config('wk-core.class.mall-shelf.stock'), 'morph', config('wk-core.table.morph-tag.tags_morphs'));
    }

    /**
     * Check if it belongs to the user.
     * 
     * @param User  $user
     * @return Bool
     */
    public function isOwnedBy($user): bool
    {
        if (empty($user))
            return false;

        return $this->host->user_id == $user->id;
    }
}
