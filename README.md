WordPress Basecamp Integration
==============================

This WordPress plugin allows you to authenticate WordPress against 37signals' Basecamp application. 

All OAuth2 options are controlled via the `Settings > Basecamp Integration` page.

How Does It Work?
-----------------

When users go to the WordPress login page (http://example.com/wp-login.php), they will be redirected immediately to Basecamp for authentication, based on the info provided in the settings page. After login, they will be redirected back and authenticated.

The user's organizations is compared against a specified organization ID. If they don't belong to that organization, authentication fails. 

If the user doesn't exist (checks against the email address), a new user will be added with the name & email provided by Basecamp. Also, if they are an administrator of the Basecamp organization, they will be given the administrator role in WordPress.

Author
------

**Brandon Wamboldt**

+ [@brandonwamboldt](http://twitter.com/brandonwamboldt)
+ [github.com/brandonwamboldt](http://github.com/brandonwamboldt)
+ [brandonwamboldt.ca](http://brandonwamboldt.ca)

License
-------

Copyright 2013 [Brandon Wamboldt](http://brandonwamboldt.ca/) under the MIT license (see LICENSE.md).
