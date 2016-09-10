<?php

namespace Ferri\LaravelSettings;

use Illuminate\Database\DatabaseManager;

class Repository
{
    /**
     * Database instance.
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * Database table to store settings.
     *
     * @var string
     */
    protected $table;

    /**
     * Any extra columns that should be added to the query.
     *
     * @var array
     */
    protected $extraColumns = [];

    /**
     * Create new database repository.
     *
     * @param \Illuminate\Database\DatabaseManager $db
     * @param string                               $table
     */
    public function __construct(DatabaseManager $db, $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * Determine if the given setting value exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->tableSelect()->where('key', '=', $key)->count() > 0 ? true : false;
    }

    /**
     * Get the specified setting value.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key)
    {
        return $this->tableSelect()->where('key', '=', $key)->value('value');
    }

    /**
     * Set a given setting value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = array_merge([
            'key' => $key,
        ], $this->extraColumns);

        $find = $this->table()->where($keys)->first();

        if (is_null($find)) {
            $this->table()->insert($keys + ['value' => $value]);
        } else {
            $this->table()->where($keys)->update(['value' => $value]);
        }
    }

    /**
     * Get all settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->tableSelect()->get();
    }

    /**
     * Forget current setting value.
     *
     * @param  string $key
     * @return void
     */
    public function forget($key)
    {
        $this->tableSelect()->where('key', $key)->delete();
    }

    /**
     * Remove all setting.
     *
     * @return void
     */
    public function flush()
    {
        $this->table()->truncate();
    }

    /**
     * Set extra columns to be added to the rows.
     *
     * @param  array $columns
     * @return $this
     */
    public function setExtraColumns(array $columns)
    {
        $this->extraColumns = $columns;

        return $this;
    }

    /**
     * Get a query builder for the settings table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->db->table($this->table);
    }

    /**
     * Get a query builder for the select table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function tableSelect()
    {
        $table = $this->table();

        foreach ($this->extraColumns as $key => $value) {
            $table->where($key, $value);
        }

        return $table;
    }
}
