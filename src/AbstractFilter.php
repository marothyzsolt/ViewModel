<?php


namespace MarothyZsolt\ViewModel;


use Illuminate\Database\Eloquent\Builder;
use MarothyZsolt\ViewModel\Parameter\ParameterController;

abstract class AbstractFilter
{
    /**
     * @var ParameterController
     */
    private $parameterController;

    /**
     * If it is set to FALSE, the last filter call (in custom filter class methods) returns not must be instance of Builder class.
     * For example if it's FALSE, the filter class can return Collection
     * Default value is TRUE
     *
     * @var boolean
     */
    protected $returnBuilder = TRUE;

    /**
     * Array of the enabled filters
     * For example: [
     *                  MethodName -> [List of URI parameters inside array]
     *              ]
     *
     * @return array
     */
    abstract protected function map() : array;

    /**
     * Filter constructor.
     * @param ParameterController $parameterController
     */
    public function __construct(ParameterController $parameterController)
    {
        if(count($this->map()) === 0)
        {
            throw new \InvalidArgumentException('Filterable array must be minimum 1 element in the Filter class: '.get_class($this));
        }

        $this->parameterController = $parameterController;
    }

    public function getFilterable()
    {
        return $this->map();
    }

    /**
     * Called by recursive from the ViewModel via Filterable class applyFilter method.
     * @param $caller
     * @return Builder|null
     * @throws \Exception
     */
    final public function apply($caller)
    {
        foreach ($this->map() as $methodName => $methodParameterNames) {
            if(!method_exists($this, $methodName)) {
                throw new \InvalidArgumentException("Method [$methodName] not initialized for filter key.");
            }

            $types = $this->getParameterListType($methodName);

            $methodParameters = array_merge([$caller], $this->parameterController->toArray($methodParameterNames));
            if(count($methodParameters) !== count($types))
            {
                throw new \Exception("Method [".get_class($this).", $methodName] parameter list (".count($types).") must same size of the given parameter list (".count($methodParameters).")");
            }
            if(count($types) === 0)
            {
                throw new \Exception("Method [".get_class($this).", $methodName] parameter list must have minimum 1 parameter, which is the Builder/Repository/Model");
            }
            if(($paramterCallerFromMethod = collect( $types)->first()) !== get_class($caller))
            {
                throw new \Exception("Method [".get_class($this).", $methodName] first parameter must be ".get_class($caller).", but given $paramterCallerFromMethod");
            }

            $tempCaller = call_user_func_array([$this, $methodName], $methodParameters);
            if($tempCaller !== NULL)
            {
                $caller = $tempCaller;
            }
        }

        if(!$caller instanceof Builder && $this->returnBuilder === TRUE)
        {
            throw new \UnexpectedValueException('The last filter call should return with Builder class. If you want to turn off this exception, you must set $returnBuilder to FALSE in Filter class ('.get_class($this).').');
        }
        return $caller;
    }

    private function getParameterListType($methodName) : array
    {
        $types = [];
        try {
            $reflectionFunc = new \ReflectionMethod($this, $methodName);
            foreach ($params = $reflectionFunc->getParameters() as $param) {
                $name = $param->getName();
                if($param->getType() !== NULL) {
                    $type = $param->getType()->getName();
                } else {
                    $type = NULL;
                }
                $types[$name] = $type;
            }
            return $types;
        } catch (\ReflectionException $e) {
            return [];
        }
    }

    private function getFirstParameterByArray(array $methods)
    {
        $firstParamTypes = [];
        foreach ($methods as $methodName => $paramList) {
            $firstType = collect($this->getParameterListType($methodName))->first();
            $firstParamTypes[$methodName] = $firstType;
        }

        return $firstParamTypes;
    }
}
