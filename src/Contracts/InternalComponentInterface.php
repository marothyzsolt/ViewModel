<?php


namespace MarothyZsolt\ViewModel\Contracts;


interface InternalComponentInterface
{
    public function build();

    public function getKey() : string;
}
