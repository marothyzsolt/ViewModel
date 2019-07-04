<?php


namespace MarothyZsolt\ViewModel\Parameter;


class ParameterController
{
    /**
     * @var array
     */
    private $parameters;

    /** @var array */
    public $params;

    /**
     * ParameterController constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
        $this->params = $this->apply();
    }


    private function apply()
    {
        $filters = [];
        foreach ($this->parameters as $filter => $value) {
            $method = is_string($filter) ? $filter : $value;
            if (($filters[$method] = head(\request()->only($value))) === false) {
                unset($filters[$method]);
            }
        }

        return $filters;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if(isset($this->params[$name]))
        {
            return $this->params[$name];
        }
        return NULL;
    }

    /**
     * @param array|string $fromParams
     * @return array
     */
    public function toArray($fromParams)
    {
        if (is_string($fromParams)) {
            return [$fromParams => $this->$fromParams];
        }

        if(is_array($fromParams)) {
            $params = [];
            foreach ($fromParams as $index => $fromParam) {
                $params[$fromParam] = $this->$fromParam;
            }
            return $params;
        }
    }

}
