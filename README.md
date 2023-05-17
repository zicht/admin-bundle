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
zicht_admin:
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
    protected function configureRoutes(RouteCollectionInterface $collection): void
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
   protected function configureRoutes(RouteCollectionInterface $collection): void
   {
       $collection->add('duplicate');
       $collection->add('override');
   }
   ```
2. In the entity create the field `copiedFrom` (and its getter and setter).
   ```
   /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Page")
    * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
    */
   private ?Page $copiedFrom;
   ```
3. In the admin of the entity, add the override-button:
    ```
    if ($this->getSubject()->getCopiedFrom()) {
        $formMapper
            ->tab('admin.tab.schedule_publication')
                ->add(
                    'copiedFrom',
                    ButtonType::class,
                    [
                        'required' => false,
                        'help' => $this->trans('admin.help.override', ['%copied_from%' => $this->getSubject()->getCopiedFrom()]),
                        'buttons' => [
                            'override' => [
                                'label' => 'admin.override.text_button',
                                'style' => 'info',
                                'route' => 'override',
                            ],
                        ],
                    ]
                )
                ->end()
            ->end();
    }
    ```

For an example, see https://github.com/zicht/zestor.nl/pull/155/files

## Quicklist
The quicklist is an autocomplete feature. In the CMS you can place this as an extra block at the dashboard to search for entities. It is also possible to use the `AutocompleteType` class in admin entities.

### Configuration
In your project, create `templates/admin/block_admin.html.twig` and add this:
```
{% extends sonata_block.templates.block_base %}

{% block block %}
    {{ render(controller('Zicht\\Bundle\\AdminBundle\\Controller\\QuicklistController::quicklistAction')) }}
{% endblock %}
```

In `config/packages/zicht/admin.yaml` you have the option to add multiple repositories to be searched through.

Example:
```yaml
zicht_admin:
    quicklist:
        App\Entity\Page\BiographyPage:
            repository: 'App\Entity\Page\BiographyPage'
            # choose multiple fields to search in...
            fields: ['firstName', 'lastName', 'profession']
            title: Bio
        App\Entity\Page\ArticlePage:
            repository: 'App\Entity\Page\ArticlePage'
            # ...or just one field
            fields: ['title']
            title: Article
        App\Entity\Slide:
            repository: 'App\Entity\Slide'
            fields: ['title', 'internalTitle', 'image']
            title: Slide
            # by default returns 15 results if not configured explicitly
            max_results: 100
```

### Implementation example
```php
namespace App\Admin;

use App\Entity\Page\BiographyPage;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Zicht\Bundle\AdminBundle\Form\AutocompleteType;

class FooAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('somePropertyNameHere', AutocompleteType::class, [
                'repo' => BiographyPage::class,
            ]);
    }
}
```


# Maintainers
* Boudewijn Schoon <boudewijn@zicht.nl>
