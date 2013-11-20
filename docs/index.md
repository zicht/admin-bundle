# ZichtAdminBundle #

## The admin menu ##
The zicht admin bundle provides an admin menu which is triggered when nearing a hotspot in the upper left corner of the
screen. You can enable this menu by adding it to your front end template:

~~~~html
{% if user and user.isAdmin %}
    <script type="text/javascript" src="{{ asset('bundles/zichtadmin/javascript/bar.js') }}"></script>
    <div id="zicht_admin_menu">{{ knp_menu_render('zicht_admin') }}</div>
{% endif %}
~~~~

## Quick lists ##

You can add quick lists to your dashboard by configuring a block which renders the quicklist action, or by adding this
to your main admin layout:

    {% render 'ZichtAdminBundle:Quicklist:quicklist' %}

This renders a list of simple forms which you can use to quickly jump to the "show" screen of a record in the database.

### Configuration ###

Example:
    zicht_admin:
        quicklist:
            page:
                repository: 'MyBundle:Page'
                fields: ['title']
                title: Jump to page
            user:
                repository: 'MyBundle:User'
                fields: ['username', 'email', 'full_name']
                title: Jump to user

Currently, only Doctrine repositories are supported. The fields specify the fields where to look for the user's input
using a LIKE query. In the above example this would result in a '...WHERE p.title LIKE :pattern' clause for the
MyBundle:Page entity, and a '...WHERE (u.username LIKE :pattern OR u.email LIKE :pattern OR u.full_name LIKE :pattern)'
for the MyBundle:User entity.

