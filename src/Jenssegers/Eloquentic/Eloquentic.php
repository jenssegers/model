<?php namespace Jenssegers\Eloquentic;

use Closure;
use ArrayAccess;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Eloquent\MassAssignmentException;

abstract class Eloquentic implements ArrayAccess {

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The model attribute's original state.
     *
     * @var array
     */
    protected $original = array();

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array();

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array();

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Indicates whether attributes are snake cased on arrays.
     *
     * @var bool
     */
    public static $snakeAttributes = true;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected static $dispatcher;

    /**
     * The array of booted models.
     *
     * @var array
     */
    protected static $booted = array();

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = false;

    /**
     * The cache of the mutated attributes for each class.
     *
     * @var array
     */
    protected static $mutatorCache = array();

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct($attributes = array())
    {
        if ( ! isset(static::$booted[get_class($this)]))
        {
            static::boot();

            static::$booted[get_class($this)] = true;
        }

        if (is_object($attributes))
        {
            $attributes = get_object_vars($attributes);
        }

        $this->fill($attributes);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        $class = get_called_class();

        static::$mutatorCache[$class] = array();

        // Here we will extract all of the mutated attributes so that we can quickly
        // spin through them after we export models to their array form, which we
        // need to be fast. This will let us always know the attributes mutate.
        foreach (get_class_methods($class) as $method)
        {
            if (preg_match('/^get(.+)Attribute$/', $method, $matches))
            {
                if (static::$snakeAttributes) $matches[1] = snake_case($matches[1]);

                static::$mutatorCache[$class][] = lcfirst($matches[1]);
            }
        }
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            // The developers may choose to place some attributes in the "fillable"
            // array, which means only those attributes may be set through mass
            // assignment to the model, and all others will just be ignored.
            if ($this->isFillable($key))
            {
                $this->setAttribute($key, $value);
            }
            elseif ($this->totallyGuarded())
            {
                throw new MassAssignmentException($key);
            }
        }

        return $this;
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newInstance($attributes = array())
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array) $attributes);

        return $model;
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);

        if (method_exists($model, 'save')) $model->save();
        
        return $model;
    }
    
    /**
     * Register a saving model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function saving(Closure $callback)
    {
        static::registerModelEvent('saving', $callback);
    }
    
    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function saved(Closure $callback)
    {
        static::registerModelEvent('saved', $callback);
    }
    
    /**
     * Register an updating model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function updating(Closure $callback)
    {
        static::registerModelEvent('updating', $callback);
    }

    /**
     * Register an updated model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function updated(Closure $callback)
    {
        static::registerModelEvent('updated', $callback);
    }

    /**
     * Register a creating model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function creating(Closure $callback)
    {
        static::registerModelEvent('creating', $callback);
    }

    /**
     * Register a created model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function created(Closure $callback)
    {
        static::registerModelEvent('created', $callback);
    }

    /**
     * Register a deleting model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function deleting(Closure $callback)
    {
        static::registerModelEvent('deleting', $callback);
    }

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function deleted(Closure $callback)
    {
        static::registerModelEvent('deleted', $callback);
    }

    /**
     * Register a model event with the dispatcher.
     *
     * @param  string   $event
     * @param  Closure  $callback
     * @return void
     */
    protected static function registerModelEvent($event, Closure $callback)
    {
        if (isset(static::$dispatcher))
        {
            $name = get_called_class();

            static::$dispatcher->listen("eloquent.{$event}: {$name}", $callback);
        }
    }

    /**
     * Fire the given event for the model.
     *
     * @param  string $event
     * @param  bool   $halt
     * @return mixed
     */
    protected function fireModelEvent($event, $halt = true)
    {
        if ( ! isset(static::$dispatcher)) return true;

        // We will append the names of the class to the event to distinguish it from
        // other model events that are fired, allowing us to listen on each model
        // event set individually instead of catching event for all the models.
        $event = "eloquent.{$event}: ".get_class($this);

        $method = $halt ? 'until' : 'fire';

        return static::$dispatcher->$method($event, $this);
    }

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set the hidden attributes for the model.
     *
     * @param  array  $hidden
     * @return void
     */
    public function setHidden(array $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Set the fillable attributes for the model.
     *
     * @param  array  $fillable
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fillable(array $fillable)
    {
        $this->fillable = $fillable;

        return $this;
    }

    /**
     * Set the guarded attributes for the model.
     *
     * @param  array  $guarded
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function guard(array $guarded)
    {
        $this->guarded = $guarded;

        return $this;
    }

    /**
     * Disable all mass assignable restrictions.
     *
     * @return void
     */
    public static function unguard()
    {
        static::$unguarded = true;
    }

    /**
     * Set "unguard" to a given state.
     *
     * @param  bool  $state
     * @return void
     */
    public static function setUnguardState($state)
    {
        static::$unguarded = $state;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     *
     * @param  string  $key
     * @return bool
     */
    public function isFillable($key)
    {
        if (static::$unguarded) return true;

        // If the key is in the "fillable" array, we can of course assume tha it is
        // a fillable attribute. Otherwise, we will check the guarded array when
        // we need to determine if the attribute is black-listed on the model.
        if (in_array($key, $this->fillable)) return true;

        if ($this->isGuarded($key)) return false;

        return empty($this->fillable) and ! starts_with($key, '_');
    }

    /**
     * Determine if the given key is guarded.
     *
     * @param  string  $key
     * @return bool
     */
    public function isGuarded($key)
    {
        return in_array($key, $this->guarded) or $this->guarded == array('*');
    }

    /**
     * Determine if the model is totally guarded.
     *
     * @return bool
     */
    public function totallyGuarded()
    {
        return count($this->fillable) == 0 and $this->guarded == array('*');
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = $this->getAccessibleAttributes();

        // We want to spin through all the mutated attribtues for this model and call
        // the mutator for the attribute. We cache off every mutated attributes so
        // we don't have to constantly check on attributes that actually change.
        foreach ($this->getMutatedAttributes() as $key)
        {
            if ( ! array_key_exists($key, $attributes)) continue;

            $attributes[$key] = $this->mutateAttribute($key, $attributes[$key]);
        }

        return $attributes;
    }

    /**
     * Get an attribute array of all accessible attributes.
     *
     * @return array
     */
    protected function getAccessibleAttributes()
    {
        return array_diff_key($this->attributes, array_flip($this->hidden));
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $inAttributes = array_key_exists($key, $this->attributes);

        // If the key references an attribute, we can just go ahead and return the
        // plain attribute value from the model. This allows every attribute to
        // be dynamically accessed through the _get method without accessors.
        if ($inAttributes or $this->hasGetMutator($key))
        {
            return $this->getAttributeValue($key);
        }
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key))
        {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes))
        {
            return $this->attributes[$key];
        }
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.studly_case($key).'Attribute');
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get'.studly_case($key).'Attribute'}($value);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key))
        {
            $method = 'set'.studly_case($key).'Attribute';

            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set'.studly_case($key).'Attribute');
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @param  bool   $sync
     * @return void
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;

        if ($sync) $this->syncOriginal();
    }

    /**
     * Get the model's original attribute values.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return array
     */
    public function getOriginal($key = null, $default = null)
    {
        return array_get($this->original, $key, $default);
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = array();

        foreach ($this->attributes as $key => $value)
        {
            if ( ! array_key_exists($key, $this->original) or $value != $this->original[$key])
            {
                $dirty[$key] = $value;
            }
        }

        return $dirty;  
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Illuminate\Events\Dispatcher
     */
    public static function getEventDispatcher()
    {
        return static::$dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param  \Illuminate\Events\Dispatcher
     * @return void
     */
    public static function setEventDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Unset the event dispatcher for models.
     *
     * @return void
     */
    public static function unsetEventDispatcher()
    {
        static::$dispatcher = null;
    }

    /**
     * Get the mutated attributes for a given instance.
     *
     * @return array
     */
    public function getMutatedAttributes()
    {
        $class = get_class($this);

        if (isset(static::$mutatorCache[$class]))
        {
            return static::$mutatorCache[get_class($this)];
        }

        return array();
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$é;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]) or isset($this->relations[$key]);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);

        unset($this->relations[$key]);
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $query = $this->newQuery();

        return call_user_func_array(array($query, $method), $parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;

        return call_user_func_array(array($instance, $method), $parameters);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

}
