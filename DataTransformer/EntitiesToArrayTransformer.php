<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GregwarFormBundle\FormBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

use Doctrine\ORM\NoResultException;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class EntitiesToArrayTransformer implements DataTransformerInterface
{
    private $em;
    private $class;
    private $queryBuilder;

    private $unitOfWork;

    public function __construct($em, $class, $queryBuilder)
    {
        if (!(null === $queryBuilder || $queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder or \Closure');
        }

        if (null == $class) {
            throw new UnexpectedTypeException($class, 'string');
        }

        $this->em = $em;
        $this->unitOfWork = $this->em->getUnitOfWork();
        $this->class = $class;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Transforms entities into choice keys
     *
     * @param Collection|object $collection A collection of entities, a single entity or
     *                                      NULL
     * @return mixed An array of choice keys, a single key or NULL
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return array();
        }

        if (!($collection instanceof Collection)) {
            throw new UnexpectedTypeException($collection, 'Doctrine\Common\Collections\Collection');
        }

        $array = array();
        
        foreach ($collection as $entity) {

            if (!$this->unitOfWork->isInIdentityMap($entity)) {
                throw new FormException('Entities passed to the choice field must be managed');
            }

            $array[] = current($this->unitOfWork->getEntityIdentifier($entity));
        }

        return $array;
    }

    /**
     * Transforms choice keys into entities
     *
     * @param  mixed $keys   An array of keys, a single key or NULL
     * @return Collection|object  A collection of entities, a single entity
     *                            or NULL
     */
    public function reverseTransform($keys)
    {
        $collection = new ArrayCollection();

        if ('' === $keys || null === $keys) {
            return $collection;
        }

        if (!is_array($keys)) {
            throw new UnexpectedTypeException($keys, 'array');
        }

        $em = $this->em;
        $class = $this->class;
        $repository = $em->getRepository($class);

        foreach ($keys as $data) {
            if ($qb = $this->queryBuilder) {
                if ($qb instanceof \Closure) {
                    $qb = $qb($repository, $data);
                }

                try {
                    $result = $qb->getQuery()->getSingleResult();
                } catch (NoResultException $e) {
                    throw new TransformationFailedException('No entities found');
                }
            } else {
                $result = $repository->find($data);
            }

            if (!$result) {
                throw new TransformationFailedException('Entity does not exists');
            }

            $collection->add($result);
        }

        return $collection;
    }
}

