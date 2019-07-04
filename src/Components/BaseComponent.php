<?php


namespace MarothyZsolt\ViewModel\Components;


use Illuminate\Database\Eloquent\Model;
use MarothyZsolt\ViewModel\Contracts\InternalComponentInterface;

abstract class BaseComponent implements InternalComponentInterface
{
    /**
     * @var ModelExtractor|null
     */
    private $modelExtractor;

    /**
     * @param ModelExtractor $modelExtractor
     */
    public function setUp(ModelExtractor $modelExtractor)
    {
        $this->modelExtractor = $modelExtractor;
    }


    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if(property_exists($this, $name) && isset($this->$name))
        {
            return $this->$name;
        }

        if(method_exists($this, $name))
        {
            $this->$name();
        }

        $extractedModelVar = $this->modelExtractor->get($name);
        if($extractedModelVar !== NULL)
        {
            return $extractedModelVar;
        }

        throw new \InvalidArgumentException("Method or property [$name] not exists in [".$this->getKey()."] main component.");
    }

    /**
     * @param Model $model
     * @param string|NULL $prepend
     * @return $this
     */
    public function extractModel(Model $model, string $prepend = "")
    {
        $this->modelExtractor->add(get_class($model), new ModelExtracted($model))->prepend($prepend);
        return $this;
    }
}
