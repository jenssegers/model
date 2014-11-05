Laravel Model
=============

[![Build Status](http://img.shields.io/travis/jenssegers/laravel-model.svg)](https://travis-ci.org/jenssegers/laravel-model) [![Coverage Status](http://img.shields.io/coveralls/jenssegers/laravel-model.svg)](https://coveralls.io/r/jenssegers/laravel-model)

This model provides an eloquent-like base class that can be used to build custom models in Laravel or other frameworks.

Example:

    class User extends Model {

        protected $hidden = array('password');

        public function save()
        {
            return API::post('/items', $this->attributes);
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
    }

    $item = new User(array('name' => 'john'));
    $item->password = 'bar';

    echo $item; // {"name":"john"}

Features
--------

 - Accessors and mutators
 - Model to Array and JSON conversion
 - Hidden attributes in Array/JSON conversion
 - Appending accessors and mutators to Array/JSON conversion

You can read more about these features and the original Eloquent model on http://four.laravel.com/docs/eloquent

Installation
------------

Install using composer:

    composer require jenssegers/model

Optaional: and add an alias to the bottom of `config/app.php`:

    'Model'           => 'Jenssegers\Model\Model',
