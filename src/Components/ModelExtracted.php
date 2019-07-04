<?php


namespace MarothyZsolt\ViewModel\Components;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModelExtracted
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var string
     */
    private $prepend;


    /**
     * ModelExtracted constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function prepend(string $prepend)
    {
        $this->prepend = $prepend;
    }


    public function get(string $name, array $args = NULL)
    {
        $name_ = $name;
        $nameAlias = "";
        if ($this->prepend !== '' && Str::startsWith($name, $this->prepend) && strlen($name) > strlen($this->prepend))
        {
            $name_ = Str::camel(substr($name, strlen($this->prepend)));
            $nameAlias = Str::snake(substr($name, strlen($this->prepend))); // For check in fillable array
        }

        $name = $name_;

        if(
            $this->model->$name !== NULL ||
            in_array($nameAlias, $this->model->getFillable(), true) ||
            in_array($name, $this->model->getFillable(), true)
        )
        {
            if(is_string($name) && $this->model->$name !== NULL)
            {
                return $this->model->$name;
            }
            if(is_string($nameAlias) && $this->model->$nameAlias !== NULL)
            {
                return $this->model->$nameAlias;
            }

            return "";
        }
        if(is_callable($this->model, $name))
        {
            return call_user_func_array([$this->model, $name], $args);
        }

        return NULL;
    }

}
