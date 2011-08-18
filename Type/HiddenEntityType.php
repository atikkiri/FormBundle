<?php

namespace NewEntityFormBundle\FormBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeCollectionListener;

use NewEntityFormBundle\FormBundle\DataTransformer\EntityToIdTransformer;
use NewEntityFormBundle\FormBundle\DataTransformer\EntitiesToArrayTransformer;

/**
 * Hidden Entity type
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class HiddenEntityType extends AbstractType
{
    protected $doctrine = null;
    protected $em = null;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if (isset($options['multiple']) && $options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeCollectionListener())
                ->prependClientTransformer(new EntitiesToArrayTransformer($this->em, $options['class'], $options['query_builder']));
        } else {
            $builder->prependClientTransformer(new EntityToIdTransformer($this->em, $options['class'], $options['query_builder']));
        }
    }

    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'em'                => null,
            'class'             => null,
            'property'          => null,
            'query_builder'     => null,
            'type'              => 'hidden',
            'hidden'            => true,
        );
        $options = array_replace($defaultOptions, $options);

        $this->em = $options['em'] ?: $this->doctrine->getEntityManager();

        if (null === $options['class']) {
            throw new \RunTimeException('You must provide a class option for the hidden entity field');
        }

        return $options;
    }

    public function getParent(array $options)
    {
        return $options['hidden'] ? 'hidden' : 'text';
    }

    public function getName()
    {
        return 'hidden_entity';
    }
}
