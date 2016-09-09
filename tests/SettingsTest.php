<?php

use Mockery as m;
use Ferri\LaravelSettings\Settings;
use Ferri\LaravelSettings\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseSettingStoreTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    protected $settings;

    public function setUp()
    {
        $this->db = $this->makeDb();

        $this->settings = new Settings(new Repository($this->db, 'settings'));
    }

    public function tearDown()
    {
        m::close();

        Capsule::schema()->drop('settings');
    }

    public function test_settings_set()
    {
        $this->settings->set('key', 'value');

        $value = $this->db->table('settings')->where('key', 'key')->value('value');

        $this->assertEquals('value', unserialize($value));
    }

    public function test_settings_set_array()
    {
        $array = ['hello' => 'world'];

        $this->settings->set('key', $array);

        $value = $this->db->table('settings')->where('key', 'key')->value('value');

        $this->assertEquals($array, unserialize($value));

        $this->assertEquals($array, $this->settings->get('key'));
    }

    public function test_settings_set_update_existing_key()
    {
        $this->settings->set('key', 'value');

        $this->assertEquals('value', $this->settings->get('key'));
        
        $this->settings->set('key', 'value2');

        $this->assertEquals('value2', $this->settings->get('key'));
    }

    public function test_settings_set_extra_columns()
    {
        $this->settings->setExtraColumns(['tenant_id' => 1]);

        $this->settings->set('key', 'value');

        $value = $this->db->table('settings')
            ->where('key', 'key')
            ->where('tenant_id', 1)
            ->value('value');

        $this->assertEquals('value', unserialize($value));
    }

    public function test_settings_has()
    {
        $this->settings->set('key', 'value');

        $this->assertTrue($this->settings->has('key'));

        $this->assertFalse($this->settings->has('key2'));
    }

    public function test_settings_has_extra_columns()
    {
        $this->settings->setExtraColumns(['tenant_id' => 1]);

        $this->settings->set('key', 'value');

        $this->assertTrue($this->settings->setExtraColumns(['tenant_id' => 1])->has('key'));

        $this->assertFalse($this->settings->setExtraColumns(['tenant_id' => 2])->has('key'));
    }

    public function test_settings_get()
    {
        $this->settings->set('key', 'value');

        $this->assertEquals('value', $this->settings->get('key'));
    }

    public function test_settings_get_with_default()
    {
        $this->assertEquals('value', $this->settings->get('key', 'value'));
    }

    public function test_settings_get_with_cache()
    {
        $cache = $this->mockCache();

        $this->settings->enableCache();
        
        $this->settings->setCache($cache);
        
        $cache->shouldReceive('forget')->once();

        $this->settings->set('key', 'value');

        $cache->shouldReceive('rememberForever')->once()->andReturn(serialize('value'));

        $this->assertEquals('value', $this->settings->get('key'));
    }

    public function test_settings_get_without_cache()
    {
        $cache = $this->mockCache();

        $this->settings->disableCache();
        
        $this->settings->setCache($cache);
        
        $this->settings->set('key', 'value');

        $cache->shouldReceive('rememberForever')->never();

        $this->assertEquals('value', $this->settings->get('key'));
    }

    public function test_settings_get_extra_columns()
    {
        $this->settings->setExtraColumns(['tenant_id' => 1]);

        $this->settings->set('key', 'value');

        $this->assertEquals('value',
            $this->settings->setExtraColumns(['tenant_id' => 1])->get('key')
        );

        $this->assertNull($this->settings->setExtraColumns(['tenant_id' => 2])->get('key'));
    }

    public function test_settings_forget()
    {
        $this->settings->set('key', 'value');

        $this->settings->forget('key');

        $this->assertNull($this->settings->get('key'));
    }

    public function test_settings_flush()
    {
        $this->settings->set('key', 'value');
        $this->settings->set('key1', 'value1');
        $this->settings->set('key2', 'value2');

        $this->settings->flush();

        $this->assertNull($this->settings->get('key'));
        $this->assertNull($this->settings->get('key1'));
        $this->assertNull($this->settings->get('key2'));
    }

    public function test_settings_check_extra_columns_removed_after_executed()
    {
        $this->settings->setExtraColumns(['tenant_id' => 1])->set('key', 'value');

        $this->assertEquals([], $this->settings->getExtraColumns());

        $this->settings->setExtraColumns(['tenant_id' => 1])->get('key');

        $this->assertEquals([], $this->settings->getExtraColumns());

        $this->settings->setExtraColumns(['tenant_id' => 1])->forget('key');

        $this->assertEquals([], $this->settings->getExtraColumns());

        $this->settings->setExtraColumns(['tenant_id' => 1])->has('key');

        $this->assertEquals([], $this->settings->getExtraColumns());
    }

    protected function makeDb()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'   => 'sqlite',
            'host'     => 'localhost',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $capsule->setAsGlobal();

        $capsule->bootEloquent();
        
        Capsule::schema()->create('settings', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $table->string('key')->index();
            $table->text('value')->nullable();
        });
        
        return $capsule->getDatabaseManager();
    }

    protected function mockCache()
    {
        return m::mock('Illuminate\Contracts\Cache\Repository');
    }
}
