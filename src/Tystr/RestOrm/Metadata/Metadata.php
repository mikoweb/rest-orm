<?php

namespace Tystr\RestOrm\Metadata;

use Tystr\RestOrm\Exception\InvalidArgumentException;
use Tystr\RestOrm\Repository\Repository;
use ReflectionClass;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class Metadata
{
    /**
     * @var ReflectionClass
     */
    private $reflClass;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $embeddedRel;

    /**
     * @var string
     */
    private $repositoryClass;

    /**
     * @param ReflectionClass $reflClass
     */
    public function __construct(ReflectionClass $reflClass)
    {
        $this->reflClass = $reflClass;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->reflClass->getName();
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param object $object
     *
     * @return string|int
     */
    public function getIdentifierValue($object)
    {
        if (get_class($object) !== $this->getClass()) {
            throw new InvalidArgumentException(sprintf('$object must be an instance of "%s".', $this->getClass()));
        }

        $property = $this->reflClass->getProperty($this->getIdentifier());
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @return string
     */
    public function getEmbeddedRel()
    {
        return $this->embeddedRel;
    }

    /**
     * @param string $embeddedRel
     */
    public function setEmbeddedRel($embeddedRel)
    {
        $this->embeddedRel = $embeddedRel;
    }

    /**
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass ?: Repository::class;
    }

    /**
     * @param string $repositoryClass
     */
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;
    }
}
