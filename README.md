NewEntityFormBundle
=====================

`NewEntityFormBundle` provides the form type "hidden_entity"

Installation
============

To install `GregwarFormBundle`, first adds it to your deps and clone it in your
vendor directory, then add the namespace to your `app/autoload.php` file:

      'NewEntityFormBundle' => __DIR__.'/../vendor/bundles/',

And registers the bundle in your `app/AppKernel.php`:

    ...
    public function registerBundles()
    {
        $bundles = array(
            ...
            new NewEntityFormBundle\FormBundle\NewEntityFormBundle(),
            ...
        );
    ...


Usage
=====

The hidden_entity is a field that contains an entity id, this assumes you set up
javascripts or any UI logics to fill it programmatically.

The usage look like the entity field type one, except that the query builder have
to returns one unique result. One full example :

    $builder->add('posts_tags', 'hidden_entity', array(
            'multiple' => true,
            'required' => false,
            'class' => 'WSL\BackendBundle\Entity\Tag',
            'query_builder' => function(EntityRepository $repo, $id) {
                return $repo->createQueryBuilder('t')
                    ->where('t.tag_id = :id')
                    ->setParameter('id', $id);
            }
        ));

Note that if you don't provide any query builder, `->find($id)` will be used.

Notes
=====

There is maybe bugs in this implementations, this package is just an idea of a form
field type which can be very useful for the Symfony2 project.

License
=======

This bundle is under MIT license
