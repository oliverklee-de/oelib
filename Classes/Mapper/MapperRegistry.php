<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a registry for mappers. The mappers must be located in
 * the directory Mapper/ in each extension. Extension can use mappers from
 * other extensions as well.
 */
class MapperRegistry
{
    /**
     * @deprecated #2287 will be removed in oelib 7.0
     */
    private static ?MapperRegistry $instance = null;

    /**
     * @var array<class-string, AbstractDataMapper> already created mappers (by class name)
     */
    private array $mappers = [];

    /**
     * Returns an instance of this class.
     *
     * @return MapperRegistry the current Singleton instance
     *
     * @deprecated #2287 will be removed in oelib 7.0; use DI instead
     */
    public static function getInstance(): MapperRegistry
    {
        if (!self::$instance instanceof self) {
            self::$instance = new MapperRegistry();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new
     * instance.
     *
     * @deprecated #2287 will be removed in oelib 7.0
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Retrieves a dataMapper by class name.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the name of an existing mapper class
     *
     * @return M the mapper instance of the provided class
     *
     * @throws \InvalidArgumentException if there is no such mapper
     *
     * @see getByClassName
     *
     * @deprecated use `getByClassName` instead. #2290 will be removed in oelib 7.0
     */
    public static function get(string $className): AbstractDataMapper
    {
        return self::getInstance()->getByClassName($className);
    }

    /**
     * Retrieves a dataMapper by class name.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the name of an existing mapper class
     *
     * @return M the mapper instance of the provided class
     *
     * @throws \InvalidArgumentException if there is no such mapper
     */
    public function getByClassName(string $className): AbstractDataMapper
    {
        if ($className === '') {
            throw new \InvalidArgumentException('$className must not be empty.', 1_331_488_868);
        }

        if (isset($this->mappers[$className])) {
            /** @var M $mapper */
            $mapper = $this->mappers[$className];
        } else {
            if (!\class_exists($className)) {
                throw new \InvalidArgumentException(
                    'No mapper class "' . $className . '" could be found.',
                    1_632_844_178,
                );
            }

            $mapper = GeneralUtility::makeInstance($className);
            $this->mappers[$className] = $mapper;
        }

        return $mapper;
    }

    /**
     * Sets a mapper that can be returned via get.
     *
     * This function is a static public convenience wrapper for setByClassName.
     *
     * This function is to be used for testing purposes only.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the class name of the mapper to set
     * @param M $mapper the mapper to set, must be an instance of `$className`
     *
     * @see setByClassName
     *
     * @deprecated use `setByClassName` instead. #2290 will be removed in oelib 7.0
     */
    public static function set(string $className, AbstractDataMapper $mapper): void
    {
        self::getInstance()->setByClassName($className, $mapper);
    }

    /**
     * Sets a mapper that can be returned via get.
     *
     * This function is to be used for testing purposes only.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the class name of the mapper to set
     * @param M $mapper the mapper to set, must be an instance of `$className`
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setByClassName(string $className, AbstractDataMapper $mapper): void
    {
        if (!$mapper instanceof $className) {
            throw new \InvalidArgumentException(
                'The provided mapper is not an instance of ' . $className . '.',
                1_331_488_915,
            );
        }

        if (isset($this->mappers[$className])) {
            throw new \BadMethodCallException(
                'There already is a ' . $className . ' mapper registered. ' .
                'Overwriting existing mappers is not allowed.',
                1_331_488_928,
            );
        }

        $this->mappers[$className] = $mapper;
    }
}
