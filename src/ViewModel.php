<?php

namespace MarothyZsolt\ViewModel;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MarothyZsolt\ViewModel\Helpers\ClassHelper;
use MarothyZsolt\ViewModel\Components\InternalComponentCollection;
use MarothyZsolt\ViewModel\Components\ModelExtractor;
use MarothyZsolt\ViewModel\Contracts\InternalComponentInterface;
use Closure;
use MarothyZsolt\ViewModel\Parameter\ParameterController;
use Prophecy\Exception\Doubler\ClassNotFoundException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

abstract class ViewModel implements Arrayable, Responsable
{
    abstract protected function build();

    protected $ignore = [];
    protected $view = '';

    /**
     * @var InternalComponentCollection
     */
    private $internalComponentCollection;

    private $customVariables;

    private $availableComponents;

    private $response;

    /**
     * @var ParameterController
     */
    protected $parameterController;

    /**
     * @var array
     */
    protected $acceptedParams = [];

    /**
     * ViewModel constructor.
     * @param array $vars
     */
    public function __construct(...$vars)
    {
        $this->parameterController = new ParameterController($this->acceptedParams);
        $this->availableComponents = array_merge(
            ClassHelper::classListByNamespace(__DIR__.'/Components'),
            ClassHelper::classListByNamespace(app_path('ViewModels/Components'))
        );

        $this->internalComponentCollection = new InternalComponentCollection();
        $this->customVariables = collect();

        if(method_exists($this, 'setUp')) {
            $this->setUp(...$vars);
        }

        $this->response = response();
    }

    public function toArray(): array
    {
        $data = $this->preBuild();
        $fullData = array_merge($this->customVariables->toArray(), $this->items()->all());

        if(count(array_intersect_key($data, $fullData)) > 0) {
            throw new UnexpectedValueException("Key already defined: " . implode(',', array_keys(array_intersect_key($data, $parentData))));
        }

        return array_merge($fullData, $data);
    }

    public function toResponse($request): Response
    {
        //$this->preBuild();

        if ($request->wantsJson()) {
            return new JsonResponse($this->items());
        }
        if ($this->view) {
            return $this->response->view($this->view, $this);
        }
        return new JsonResponse($this->items());
    }

    public function view(string $view): ViewModel
    {
        $this->view = $view;
        return $this;
    }

    protected function items(): Collection
    {
        $class = new ReflectionClass($this);
        $publicProperties = collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(function (ReflectionProperty $property) {
                return $this->shouldIgnore($property->getName());
            })
            ->mapWithKeys(function (ReflectionProperty $property) {
                return [$property->getName() => $this->{$property->getName()}];
            });
        $publicMethods = collect($class->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(function (ReflectionMethod $method) {
                return $this->shouldIgnore($method->getName());
            })
            ->mapWithKeys(function (ReflectionMethod $method) {
                return [$method->getName() => $this->createVariableFromMethod($method)];
            });
        return $publicProperties->merge($publicMethods);
    }

    protected function shouldIgnore(string $methodName): bool
    {
        if (Str::startsWith($methodName, '__')) {
            return true;
        }
        return in_array($methodName, $this->ignoredMethods());
    }

    protected function ignoredMethods(): array
    {
        return array_merge([
            'toArray',
            'toResponse',
            'view',
            'build', 'addComponent', 'setup', 'addVar', 'availableComponents', 'customVariables', 'ignoredViewModelVarsMethods', 'modelExtractor'
        ], $this->ignore);
    }

    protected function createVariableFromMethod(ReflectionMethod $method)
    {
        if ($method->getNumberOfParameters() === 0) {
            return $this->{$method->getName()}();
        }
        return Closure::fromCallable([$this, $method->getName()]);
    }


    public function __call(string $name, array $arguments)
    {
        if(Str::startsWith($name, 'with')) {
            foreach ($this->availableComponents as $availableComponent) {
                $onlyClassName = Str::lower(last(explode('\\', $availableComponent)));
                $formattedName = Str::lower(str_replace('with', '', $name));
                if ($onlyClassName === $formattedName) {
                    $componentKeys = config('viewmodel.components');
                    $customComponentKey = $componentKeys[$formattedName] ?? NULL;
                    $componentClass = new $availableComponent();
                    $this->addComponent($componentClass, $customComponentKey);
                }
            }
        }

        return $this;
    }

    private function preBuild()
    {
        $this->build();

        $mergableArray = [];
        foreach ($this->internalComponentCollection as $key => $item) {
            $mergableArray[$key] = $item;
        }

        return $mergableArray;
    }

    protected function addComponent(InternalComponentInterface $item, string $key = NULL)
    {
        if($key === null || strlen($key) < 2) {
            if($this->internalComponentCollection->haskey($key)) {
                throw new UnexpectedValueException("Key already defined in array: " . $key);
            }
        }
        $shouldKey = ($key ?? $item->getKey());

        $item->setUp(new ModelExtractor());
        $item->build();

        $this->internalComponentCollection->put($shouldKey, $item);
        return $this;
    }

    protected function hasComponent($componentName)
    {
        return $this->internalComponentCollection->hasKey($componentName);
    }

    protected function getComponent($componentName)
    {
        return $this->internalComponentCollection->get($componentName);
    }

    public function addVar($key, $value)
    {
        $this->customVariables->put($key, $value);
    }

    public function response()
    {
        return $this->response;
    }



}
