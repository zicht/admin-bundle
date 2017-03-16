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

# Maintainer
* Rik van der Kemp <rik@zicht.nl>
* Muhammed Akbulut <muhammed@zicht.nl>

