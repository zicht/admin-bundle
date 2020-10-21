# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security

## 6.0.4 - 2020-10-20
### Removed
- Unneeded dependency declaration for `doctrine/doctrine-bundle`.
### Changed
- Allow `zicht_admin.controller.rc` to be a public service.

## 6.0.3 - 2020-09-30
### Fixed
- Merge from 5.2.3

## 6.0.2 - 2020-07-13
### Fixed
- Merge from 5.2.2.

## 6.0.1 - 2020-06-03
### Fixed
- Forward merge of 5.2.1

## 6.0.0 - 2020-05-15
### Added
- Support for Symfony 4.x
### Removed
- Support for Symfony 3.x
### Fixed
- Fixed custom sorting issue in Tree Admin caused by sonata-project/doctrine-orm-admin-bundle 3.15
### Changed
- Removed Zicht(Test)/Bundle/AdminBundle/ directory depth: moved all code up directly into src/ and test/

## 5.2.3 - 2020-09-29
### Changed
- Increased DX (Developer Experience) and modified output of DumpRoleHierarchyCommand.

Before
```
'app/console' '--env=development' 'zicht:admin:dump-role-hierarchy' 'DUPLICATE' 'TRANSLATE' '--root=ROLE_ROOT'
```

After
```
app/console --env=development zicht:admin:dump-role-hierarchy DUPLICATE TRANSLATE --root=ROLE_ROOT
```

## 5.2.2 - 2020-07-10
### Fixed
- Added missing translations

## 5.2.1 - 2020-06-03
### Fixed
- Fixed missing name for ExportSqlCommand
- Fixed `Sonata\Export\Writer\TypedWriterInterface` use statement in `src/Zicht/Bundle/AdminBundle/Exporter/Writer/TwigWriter.php`

## 5.2.0 - 2020-05-14
### Fixed
- Fixed composer.json (psr-4 autoloader, dependencies), fixed linter errors, fixed tests

## 5.1.2 - 2020-05-04
### Fixed
- Prefixed the Admin bundle Controller routes with /admin to place /quick-list within the admin path and prevent unauthorised user to request the quick list

## 5.1.1 - 2020-04-29
### Changed
- Use FQCN for form types

## 5.1.0 - 2019-12-23
### Added
- `AdminMenu::EventPropagationBuilder` copied from `ZichtDevBundle`. With this builder it is possible to add a link to the same page on different hosts.
### Removed
- Duplicate key for `path` in `zicht_admin_redirect`

## 5.0.6 - 2019-04-04
### Changed
- Tweaked the interface for initial state and searchresults with placeholder and scrollable results

## 5.0.4, 5.0.5
### Added
- Keeping BC for sensio/framework-extra-bundle^5 where folder names are lowercased
- Prevent sensio/framework-extra-bundle > 6

## 5.0.3 - 2018-10-08
### Added
- Add CSRF token to login screen

## 5.0.0 - 2018-06-26
### Added
- Support for Symfony 3.x
### Removed
- Support for Symfony 2.x

## 4.1.0 - 2020-05-04
- Fix quick list admin route (merged in from v3.4.10)
- fix the flash_message translation so we can change the default message in ZHL (merged in from 3.5.6)
- Export: add support for twig for writer and extra options for exporters

## 4.0.4
- Fix translation domain

## 4.0.3
- Update composer.json

## 4.0.2
- BC fix for allowing both sonata-admin 2 and 3

## 4.0.1
- fix for php > 7.1

## 3.5.7 - 2020-05-04
- Prefixed the Admin bundle Controller routes with /admin to place /quick-list within the admin path and prevent unauthorised user to request the quick list

## 3.4.9
- Fix the translation for the duplicate flash_message

## 3.4.8
- Fix issue with an Exception being thrown when transforming an entity that didn't exsist anymore (no id or value)

## 3.4.0 
- Includes the 2.4.0 changes from 2.x

## 3.2.2

- Bugfix: IE throws a JS error with delegate event attacher

## Version 3.2.2

- Bugfix: IE throws a JS error with delegate event attacher

## Version 3.0.0
### Breaking Changes
- login_check path needs to be prefixed with /admin/:

    form_login:
            login_path: /login
            check_path: /admin/login_check #<--- this line is changed!

    All the other paths are still the same (/login and /logout)

## 2.4.0
- Add feature in 'rc' component to toggle some configuration flag with DELETE and POST requests.

## 2.1.0
### New features
- added login form
  to configure, add the following to the security.yml:

    admin:
        pattern: ^/
        switch_user:        true
        context:            user
        anonymous:    true
        form_login:
            login_path: /login
            check_path: /login_check
        logout:
            path:   /logout

  Actually, only the following part needs to be replaced:

        http_basic:
            realm: "Secured Area"

  with:

        form_login:
            login_path: /login
            check_path: /login_check
        logout:
            path:   /logout


## Previous versions ##
No idea what changed, check the svn logs ^^
