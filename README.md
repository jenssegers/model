Laravel Model [![Build Status](https://travis-ci.org/jenssegers/Laravel-Model.png?branch=master)](https://travis-ci.org/jenssegers/Laravel-Model)
=============

This model provides an eloquent-like base class that can be used to build custom models.

Example:

```php
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
```

Features
--------

 - Array and JSON conversion
 - Accessors and mutators
 - Hidden attributes

You can read more about these features and the original Eloquent model on http://four.laravel.com/docs/eloquent

Installation
------------

Add the package to your `composer.json` or install manually.

```yaml
{
    "require": {
        "jenssegers/model": "*"
    }
}
```

Run `composer update` to download and install the package.

Add the service provider in `app/config/app.php`:

```php
'Jenssegers\Model\ModelServiceProvider',
```

And add an alias:

```php
'Model'           => 'Jenssegers\Model\Model',
```
