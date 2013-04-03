Eloquentic
==========

Eloquentic provides an eloquent-like model base class that can be used to build custom models.

Example:

    class MyModel extends Eloquentic {

    	protected $fillable = array('name');
    	protected $hidden = array('secret');

    	public function save() 
    	{
    		return API::post('/items', $this->attributes);
    	}

    	public function getNameAttribute($value)
	    {
	        return ucfirst($value);
	    }
    }

    $item = new MyModel(array('name' => 'hello'));
    $item->secret = "world";

    $item->save();

    // this will show a JSON object
    echo $item;

Features
--------

 - Automatic array and JSON conversion (castable)
 - Hiding attributes from Array or JSON conversion
 - Mass Assignment: fillable, guarded
 - Accessors & Mutators
 - Boot method for bindings

You can read more about Eloquent on http://four.laravel.com