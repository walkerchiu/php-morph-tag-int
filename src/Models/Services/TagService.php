<?php

namespace WalkerChiu\MorphTag\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class TagService
{
    use CheckExistTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.morph-tag.tagRepository'));
    }

    /**
     * @param String  $host_type
     * @param Int     $host_id
     * @param String  $code
     * @return Array
     */
    public function listOption(?string $host_type, ?int $host_id, string $code): array
    {
        return $this->repository->listOption($host_type, $host_id, $code);
    }

    /**
     * @param Blog    $blog
     * @param String  $code
     * @param Bool    $transform
     * @return Array
     */
    public function loadOptions($blog, $code = null, $transform = true): array
    {
        if (empty($code))
            $code = config('app.locale');

        $result = [];
        $tags = $blog->tags()->get();

        foreach ($tags as $tag) {
            if ($transform)
                array_push($result, [
                    'value'      => $tag->id,
                    'identifier' => $tag->identifier,
                    'label'      => $tag->findLang($code, 'name')
                ]);
            else
                array_push($result, [
                    'id'         => $tag->id,
                    'identifier' => $tag->identifier,
                    'name'       => $tag->findLang($code, 'name')
                ]);
        }

        return $result;
    }

    /**
     * @param Bool    $isOwner
     * @param Entity  $entity
     * @param String  $code
     * @param Bool    $transform
     * @return Array
     */
    public function loadOptionsSelected(bool $isOwner, $entity, $code = null, $transform = true): array
    {
        if (empty($code))
            $code = config('app.locale');

        $result = [];
        $tags = $isOwner
                    ? $entity->tags()->get()
                    : $entity->tags(true)->get();

        foreach ($tags as $tag) {
            if ($transform)
                array_push($result, [
                    'value'      => $tag->id,
                    'identifier' => $tag->identifier,
                    'label'      => $tag->findLang($code, 'name')
                ]);
            else
                array_push($result, [
                    'id'         => $tag->id,
                    'identifier' => $tag->identifier,
                    'name'       => $tag->findLang($code, 'name')
                ]);
        }

        return $result;
    }
}
