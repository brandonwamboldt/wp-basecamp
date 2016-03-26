WordPress Basecamp Integration
==============================

This WordPress plugin allows you to authenticate WordPress against 37signals' Basecamp application.

All OAuth2 options are controlled via the `Settings > Basecamp Integration` page.

How Does It Work?
-----------------

Once the settings are configured, when users go to the WordPress login page (http://example.com/wp-login.php), they will be redirected immediately to Basecamp for authentication. After login, they will be redirected back and authenticated.

If the user doesn't exist (checks against the email address), a new user will be added with the name & email provided by Basecamp.

Also, if they are an administrator of the Basecamp organization, they will be given the administrator role in WordPress.

The user's organizations is compared against a specified organization ID.

* If they don't belong to that organization, they will be granted the `'subscriber'` role.
* If they DO belong to the organization, they will be granted the `'contributor'` role.
* if they are an administrator of the Basecamp organization, they will be given the '`administrator`' role.

Each of these role defaults can be modifed through the use of the following WordPress filters:

* `'wp_basecamp_default_user_level'`
* `'wp_basecamp_default_organization_user_level'`
* `'wp_basecamp_user_level_organization_admin'`

So, to change the default role for users in your organization from `'contributor'` to `'author'`, add this snippet to your theme's functions.php file:

```php
function wp_basecamp_default_organization_user_level_to_author( $role ) {
	return 'author';
}
add_filter( 'wp_basecamp_default_organization_user_level', 'wp_basecamp_default_organization_user_level_to_author' );
```

You can also completely disable Basecamp authentication for users who do not belong to your organization, by adding the following snippet:

```php
add_filter( 'wp_basecamp_default_user_level', '__return_false' );
```

Keep in mind, these roles will be applied when the user first logs in via Basecamp and their user is created in WordPress. After that first login, you can change their user-level, and it will apply for subsequent logins.

### Additional Filters

Filter for disabling the auto-redirect on wp-login. Once disabling, users will instead be provided with a "Sign in with your Basecamp account" link in the wp-login login form, which, when clicked, will take the user to Basecamp for authentication. To disable the auto-redirect:

```php
add_filter( 'wp_basecamp_auto_redirect_login', '__return_false' );
```

Filter for modifying the auto-created usernames. By default it creates the username in the pattern of "first-last". If it finds a user in WordPress with that username, it will then append the Basecamp userid to the username, "first-last-5555555". The filter gets passed the array of identity information provided from the API, so if you preferred to change the username to the user's email, you could do so like:

```php
function wp_basecamp_username_email( $username, $identity ) {
	if ( isset( $identity['email_address'] ) ) {
		$username = $identity['email_address'];
	}

	return $username;
}
add_filter( 'wp_basecamp_username', 'wp_basecamp_username_email', 10, 2 );
```

Requirements
------------

The OAuth2 library in use in this plugin requires the [PHP cURL](http://www.php.net/manual/en/book.curl.php) extension to be installed.

Author
------

**Brandon Wamboldt**

+ [@brandonwamboldt](http://twitter.com/brandonwamboldt)
+ [github.com/brandonwamboldt](http://github.com/brandonwamboldt)
+ [brandonwamboldt.ca](http://brandonwamboldt.ca)

**Justin Sternberg**

+ [@jtsternberg](http://twitter.com/jtsternberg)
+ [github.com/jtsternberg](http://github.com/jtsternberg)
+ [dsgnwrks.pro](http://dsgnwrks.pro)

License
-------

Copyright 2013 [Brandon Wamboldt](http://brandonwamboldt.ca/) under the MIT license (see LICENSE.md).
