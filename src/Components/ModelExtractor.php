<?php


namespace MarothyZsolt\ViewModel\Components;


class ModelExtractor
{
    /**
     * @var array
     */
    protected $extractModels = [];

    public function get(string $name, array $args = NULL)
    {
        foreach ($this->extractModels as $extractModel) {
            $extractedVar = $extractModel->get($name, $args);
            if($extractedVar !== NULL)
            {
                return $extractedVar;
            }
        }
        return NULL;
    }

    public function add(string $name, ModelExtracted $class)
    {
        $this->extractModels[$name] = $class;
        return $class;
    }

    public function remove(string $name)
    {
        if(array_key_exists($name, $this->extractModels))
        {
            unset($this->extractModels[$name]);
        }
        return $this;
    }
}
