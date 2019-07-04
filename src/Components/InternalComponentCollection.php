<?php


namespace MarothyZsolt\ViewModel\Components;


use ArrayIterator;
use IteratorAggregate;
use Traversable;

class InternalComponentCollection implements IteratorAggregate
{

    private $items = [];

    public function push($item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function put($key, $item)
    {
        if($key === NULL) {
            $this->push($item);
        }
        else {
            $this->items[$key] = $item;
        }
        return $this;
    }

    public function hasKey($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->items[$key] ?? NULL;
    }


    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}
