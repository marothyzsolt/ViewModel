<?php


namespace MarothyZsolt\ViewModel\Traits;


use Illuminate\Database\Eloquent\Builder;
use MarothyZsolt\ViewModel\AbstractFilter;
use Prophecy\Exception\Doubler\ClassNotFoundException;

trait Filterable
{
    /**
     * @var AbstractFilter
     */
    protected $filter;


    public function filter($filter, array $filterVariables = [])
    {
        if(!class_exists($filter))
        {
            throw new ClassNotFoundException("Selected Filter Class [$filter] not found.", $filter);
        }

        $filterClass = new $filter($this->parameterController);
        assert($filterClass instanceof AbstractFilter);
        $this->filter = $filterClass;

        $this->fillAcceptedParams($filterClass->getFilterable());
        $this->generateFilterClassVariables($filterVariables);

        return $this;
    }

    /**
     * @param $caller
     * @param null $callback
     * @return Builder
     */
    protected function applyFilter($caller)
    {
        if(!$this->filter instanceof AbstractFilter)
        {
            $classFilter = $this->filter === NULL ? 'NULL' : get_class($this->filter);
            throw new \Exception('The filter class must instance of '.AbstractFilter::class.", but got ".$classFilter." You have to specify the filter class in controller with ViewModel->filter(ClassName::class)");
        }
        return $this->filter->apply($caller);
    }

    private function generateFilterClassVariables($filterVariables)
    {
        if(!is_array($filterVariables))
        {
            throw new \InvalidArgumentException("Filter Variables must be array.");
        }

        foreach ($filterVariables as $variableName => $filterVariableValue) {
            if(!is_string($variableName) || is_numeric($variableName))
            {
                throw new \InvalidArgumentException("Filter Variable array index must be a string. [varName => varValue]");
            }
            $this->filter->$variableName = $filterVariableValue;
        }
    }

    private function fillAcceptedParams($filterableValues) {
        foreach ($filterableValues as $index => $item) {
            if(!in_array($item, $this->acceptedParams, true))
            {
                $this->acceptedParams[] = $item;
            }
        }
    }
}
