<?php

use Jenssegers\Model\Model;

class ModelStub extends Model
{
    protected $hidden = ['password'];

    protected $casts = [
        'age'   => 'integer',
        'score' => 'float',
        'data'  => 'array',
        'active' => 'bool',
        'secret' => 'string',
        'count' => 'int',
        'object_data' => 'object',
        'collection_data' => 'collection',
        'foo' => 'bar',
    ];

    protected $guarded = [
        'secret',
    ];

    protected $fillable = [
        'name',
        'city',
        'age',
        'score',
        'data',
        'active',
        'count',
        'object_data',
        'default',
        'collection_data',
    ];

    public function getListItemsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setListItemsAttribute($value)
    {
        $this->attributes['list_items'] = json_encode($value);
    }

    public function setBirthdayAttribute($value)
    {
        $this->attributes['birthday'] = strtotime($value);
    }

    public function getBirthdayAttribute($value)
    {
        return date('Y-m-d', $value);
    }

    public function getAgeAttribute($value)
    {
        $date = DateTime::createFromFormat('U', $this->attributes['birthday']);

        return $date->diff(new DateTime('now'))->y;
    }

    public function getTestAttribute($value)
    {
        return 'test';
    }
}
