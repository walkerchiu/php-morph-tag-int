<?php

namespace WalkerChiu\MorphTag;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\MorphTag\Models\Entities\Tag;
use WalkerChiu\MorphTag\Models\Entities\TagLang;

class TagTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\MorphTag\MorphTagServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Tag.
     *
     * For WalkerChiu\MorphTag\Models\Entities\Tag
     * 
     * @return void
     */
    public function testMorphTag()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-tag.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-tag.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-tag.soft_delete', 1);

        // Give
        $record_1 = factory(Tag::class)->create();
        $record_2 = factory(Tag::class)->create();
        $record_3 = factory(Tag::class)->create(['is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Tag::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $record_2->delete();
            $records = Tag::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Tag::withTrashed()
               ->find(2)
               ->restore();
            $record_2 = Tag::find(2);
            $records = Tag::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);

        // Return Lang class
            // When
            $class = $record_2->lang();
            // Then
            $this->assertEquals($class, TagLang::class);

        // Scope query on enabled records
            // When
            $records = Tag::ofEnabled()
                          ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Tag::ofDisabled()
                          ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
