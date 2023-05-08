## 3.5.8 - 2023-05-08
### Added
- Quicklist integer option `max_results` (15 by default).

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
