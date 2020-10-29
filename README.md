[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zicht/admin-bundle/badges/quality-score.png?b=3.5.x)](https://scrutinizer-ci.com/g/zicht/admin-bundle/?branch=3.5.x)
[![Code Coverage](https://scrutinizer-ci.com/g/zicht/admin-bundle/badges/coverage.png?b=3.5.x)](https://scrutinizer-ci.com/g/zicht/admin-bundle/?branch=3.5.x)
[![Build Status](https://scrutinizer-ci.com/g/zicht/admin-bundle/badges/build.png?b=3.5.x)](https://scrutinizer-ci.com/g/zicht/admin-bundle/build-status/3.5.x)

# `zicht/admin-bundle`
Provides integration utilities for SonataAdminBundle.

## Enable dump-role-hierarchy
To make this command supply you with an actual list of roles, add the following to your `sonata_admin.yml`:
```yaml
parameters:
    sonata.admin.security.handler.role.class: Zicht\Bundle\AdminBundle\Security\Handler\RoleSecurityHandler

sonata_admin:
    security:
        handler: sonata.admin.security.handler.role
```

## Override Menu-events to supply other hosts
Add the following configuration to `config/zicht_admin.yml` to override the `AdminEvents::MENU_EVENT` 
and alter the url to a match in the list.

```yaml
zicht_admin
    menu:
        hosts:
            - site.nl.dev
            - site.nl.dev3.zicht.intern
            - a.site.nl
```

## Duplicate entities
To duplicate an entity, add the following code:
1. In the admin of the entity you want to duplicate, add the route:
    ```
    /**
     * {@inheritDoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('duplicate');
    }
    ```

2. In `templates/bundles/SonataAdminBundle/CRUD/edit.html.twig` add the duplicate button:
    ```
    {% if admin.hasroute('duplicate') %}
        <a class="btn btn-info" href="{{ admin.generateObjectUrl('duplicate', object) }}">{{ 'admin.duplicate.text_button'|trans }}</a>
    {% endif %}
    ```
For an example, see https://github.com/zicht/zestor.nl/pull/155/files

## Override entities
To also override the entities content (after duplication, see section above), add the following code:
1. Add the route in the admin so the configureRoute method becomes:
   ```
   /**
    * {@inheritDoc}
    */
   protected function configureRoutes(RouteCollection $collection)
   {
       $collection->add('duplicate');
       $collection->add('override');
   }
   ```
2. In the entity create the field `copiedFrom` (and its getter and setter).
   ```
   /**
    * @var Page
    * @ORM\ManyToOne(targetEntity="App\Entity\Page")
    * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
    */
   private $copiedFrom;
   ```
3. In the admin of the entity, add the override-button:
    ```
    if ($this->getSubject()->getCopiedFrom()) {
        $formMapper
            ->tab('admin.tab.schedule_publication')
                ->add(
                    'copiedFrom',
                    OverrideObjectType::class,
                    [
                        'required' => false, 
                        'object' => $this->getSubject(),
                        'help' => $this->trans('admin.help.override', ['%copied_from%' => $this->getSubject()->getCopiedFrom()])
                    ]
                )
                ->end()
            ->end();
    }
    ```

For an example, see https://github.com/zicht/zestor.nl/pull/155/files

# Maintainers
* Boudewijn Schoon <boudewijn@zicht.nl>
* Erik Trapman <erik@zicht.nl>
