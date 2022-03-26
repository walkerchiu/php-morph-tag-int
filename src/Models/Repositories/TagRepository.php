<?php

namespace WalkerChiu\MorphTag\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormHasHostTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryHasHostTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class TagRepository extends Repository
{
    use FormHasHostTrait;
    use RepositoryHasHostTrait;

    protected $instance;
    protected $morphType;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance  = App::make(config('wk-core.class.morph-tag.tag'));
        $this->morphType = App::make(config('wk-core.class.morph-tag.morphType'))::getCodes('relation');
    }

    /**
     * @param String  $host_type
     * @param Int     $host_id
     * @param String  $code
     * @param Array   $data
     * @param Bool    $is_enabled
     * @param String  $target
     * @param Bool    $target_is_enabled
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(?string $host_type, ?int $host_id, string $code, array $data, $is_enabled = null, $target = null, $target_is_enabled = null, $auto_packing = false)
    {
        if (
            empty($host_type)
            || empty($host_id)
        ) {
            $instance = $this->instance;
        } else {
            $instance = $this->baseQueryForRepository($host_type, $host_id, $target, $target_is_enabled);
        }
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $data = array_map('trim', $data);
        $repository = $instance->with(['langs' => function ($query) use ($code) {
                                    $query->ofCurrent()
                                          ->ofCode($code);
                                }])
                                ->whereHas('langs', function ($query) use ($code) {
                                    return $query->ofCurrent()
                                                 ->ofCode($code);
                                })
                                ->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['serial']), function ($query) use ($data) {
                                                return $query->where('serial', $data['serial']);
                                            })
                                            ->unless(empty($data['identifier']), function ($query) use ($data) {
                                                return $query->where('identifier', $data['identifier']);
                                            })
                                            ->unless(empty($data['order']), function ($query) use ($data) {
                                                return $query->where('order', $data['order']);
                                            })
                                            ->unless(empty($data['name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'name')
                                                          ->where('value', 'LIKE', "%".$data['name']."%");
                                                });
                                            })
                                            ->unless(empty($data['description']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'description')
                                                          ->where('value', 'LIKE', "%".$data['description']."%");
                                                });
                                            });
                                })
                                ->orderBy('order', 'ASC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-morph-tag.output_format'), config('wk-morph-tag.pagination.pageName'), config('wk-morph-tag.pagination.perPage'));
            $factory->setFieldsLang(['name', 'description']);
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param String  $host_type
     * @param Int     $host_id
     * @param String  $code
     * @return Array
     */
    public function listOption(?string $host_type, ?int $host_id, string $code): array
    {
        if (
            empty($host_type)
            || empty($host_id)
        ) {
            $instance = $this->instance;
        } else {
            $instance = $this->baseQueryForRepository($host_type, $host_id);
        }
        $records = $instance->with(['langs' => function ($query) use ($code) {
                                $query->ofCurrent()
                                      ->ofCode($code);
                                }])
                            ->ofEnabled()
                            ->orderBy('order', 'ASC')
                            ->select('id', 'serial', 'identifier', 'order')
                            ->get();
        $list = [];
        foreach ($records as $record) {
            $list[$record->id] = [
                'serial'      => $record->serial,
                'identifier'  => $record->identifier,
                'name'        => $record->findLangByKey('name'),
                'description' => $record->findLangByKey('description')
            ];
        }

        return $list;
    }

    /**
     * @param String  $host_type
     * @param Int     $host_id
     * @param String  $code
     * @param String  $target
     * @param Bool    $target_is_enabled
     * @return Array
     */
    public function listTag(?string $host_type, ?int $host_id, string $code, ?string $target, ?bool $target_is_enabled): array
    {
        if (
            empty($host_type)
            || empty($host_id)
        ) {
            $instance = $this->instance;
        } else {
            $instance = $this->baseQueryForRepository($host_type, $host_id, $target, $target_is_enabled);
        }
        $records = $instance->with(['langs' => function ($query) use ($code) {
                                $query->ofCurrent()
                                      ->ofCode($code);
                                }])
                            ->ofEnabled()
                            ->orderBy('order', 'ASC')
                            ->select('id', 'serial', 'identifier', 'order')
                            ->get();
        $list = [];
        foreach ($records as $record) {
            $data = [
                'id'          => $record->id,
                'serial'      => $record->serial,
                'identifier'  => $record->identifier,
                'order'       => $record->order,
                'name'        => $record->findLangByKey('name'),
                'description' => $record->findLangByKey('description')
            ];

            array_push($list, $data);
        }

        return $list;
    }

    /**
     * @param Tag           $instance
     * @param Array|String  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
        $data = [
            'id' => $instance ? $instance->id : '',
            'basic' => []
        ];

        if (empty($instance))
            return $data;

        $this->setEntity($instance);

        if (is_string($code)) {
            $data['basic'] = [
                  'host_type'   => $instance->host_type,
                  'host_id'     => $instance->host_id,
                  'serial'      => $instance->serial,
                  'identifier'  => $instance->identifier,
                  'order'       => $instance->order,
                  'name'        => $instance->findLang($code, 'name'),
                  'description' => $instance->findLang($code, 'description'),
                  'is_enabled'  => $instance->is_enabled,
                  'updated_at'  => $instance->updated_at
            ];

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                      'host_type'   => $instance->host_type,
                      'host_id'     => $instance->host_id,
                      'serial'      => $instance->serial,
                      'identifier'  => $instance->identifier,
                      'order'       => $instance->order,
                      'name'        => $instance->findLang($language, 'name'),
                      'description' => $instance->findLang($language, 'description'),
                      'is_enabled'  => $instance->is_enabled,
                      'updated_at'  => $instance->updated_at
                ];
            }
        }

        return $data;
    }
}
