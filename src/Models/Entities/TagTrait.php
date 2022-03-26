<?php

namespace WalkerChiu\MorphTag\Models\Entities;

trait TagTrait
{
    /**
     * Get all of the tags for the object.
     *
     * @param Bool  $is_enabled
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags($is_enabled = null)
    {
        $table = config('wk-core.table.morph-tag.tags_morphs');
        return $this->morphToMany(config('wk-core.class.morph-tag.tag'), 'morph', $table)
                    ->unless( is_null($is_enabled), function ($query) use ($is_enabled) {
                        return $query->where('is_enabled', $is_enabled);
                    });
    }

    /**
     * Checks if the object has a tag.
     *
     * @param String|Array  $value
     * @return Bool
     */
    public function hasTag($value): bool
    {
        if (is_string($value)) {
            return $this->tags()->where('identifier', $value)
                                ->exists();
        } elseif (is_array($value)) {
            return $this->tags()->whereIn('identifier', $value)
                                ->exists();
        }

        return false;
    }

    /**
     * Checks if the object has tags in the same time.
     *
     * @param Array  $tags
     * @return Bool
     */
    public function hasTags(array $tags): bool
    {
        $result = false;

        foreach ($tags as $tag) {
            $result = $this->tags()->where('identifier', $tag)
                                   ->exists();
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param Mixed  $tag
     * @return void
     */
    public function attachTag($tag)
    {
        if (is_object($tag)) {
            $tag = $tag->getKey();
        }

        if (is_array($tag)) {
            $tag = $tag['id'];
        }

        $this->tags()->detach($tag);
        $this->tags()->attach($tag);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param Mixed  $tag
     * @return void
     */
    public function detachTag($tag)
    {
        if (is_object($tag)) {
            $tag = $tag->getKey();
        }

        if (is_array($tag)) {
            $tag = $tag['id'];
        }

        $this->tags()->detach($tag);
    }

    /**
     * Attach multiple tags to an object
     *
     * @param Mixed  $tags
     * @return void
     */
    public function attachTags($tags)
    {
        foreach ($tags as $tag) {
            $this->detachTag($tag);
            $this->attachTag($tag);
        }
    }

    /**
     * Detach multiple tags from an object
     *
     * @param Mixed  $tags
     * @return void
     */
    public function detachTags($tags = null)
    {
        if (!$tags) {
            $tags = $this->tags()->get();
        }

        foreach ($tags as $tag) {
            $this->detachTag($tag);
        }
    }
}
