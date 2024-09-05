# Captain Hook

Captain Hook is a library for WordPress developers to help them manipulate objects and methods hooked into actions and filters using otherwise-inaccessible object instances.

## About

Oftentimes, a plugin will create an instance of an object in a function scope, or otherwise simply outside of the global scope, and then use that instance to hook methods into actions or filters. Here's an example:

```php
class Plugin_Main {
  public function __construct(
    protected \Logger $logger
  ) {
    add_action( 'init', [ $this, 'init' ], 1 );
    add_filter( 'the_content', [ $this, 'filter_the_content' ], 99 );
  }

  public function init() {
    register_post_type( 'book' );
    register_taxonomy( 'genre', 'book' );
  }

  public function filter_the_content( $content ) {
    if ( preg_match_all( '/\[([^\)]+)\]\((\d+)\)/', $content, $matches, PREG_SET_ORDER ) ) {
      foreach ( $matches as $match ) {
        $book = get_post( $match[2] );
        if ( $book ) {
          $content = str_replace( $match[0], '<a href="' . get_permalink( $book ) . '">' . $match[1] . '</a>', $content );
        } else {
          $this->logger->error( 'Book not found: ' . $match[2] );
        }
      }
    }
    return $content;
  }

  public function get_logger() {
    return $this->logger;
  }

  public function set_logger( \Logger $logger ) {
    $this->logger = $logger;
  }
}
add_action( 'after_setup_theme', function () {
  new Plugin_Main( new \Logger( 'path/to/file.log' ) );

} );
```

If a developer wanted to unhook or otherwise alter the `init` method from the `init` action, they would need to have access to the `$plugin` instance. This is not possible from outside the scope of the anonymous function that created the instance.

Captain Hook provides a way for developers to access and manipulate these objects and methods, even when they are not accessible from the global scope. Features of this library include:

- **Remove Action or Filter by Force**: Remove an action or filter from a hook.
- **Reprioritize Actions and Filters**: Change the priority of an action or filter method.
- **Retrieve an object instance**: Get the otherwise-inaccessible object instance for a method hooked into an action or filter.

## Installation

Install the latest version with:

```bash
$ composer require alleyinteractive/wp-captain-hook
```

## Basic usage

### Remove Action or Filter by Force

```php
<?php
use function Alley\WP\remove_action_by_force;
use function Alley\WP\remove_filter_by_force;

remove_action_by_force( 'init', [ '\Plugin_Main', 'init' ], 1 );
remove_filter_by_force( 'the_content', [ '\Plugin_Main', 'filter_the_content' ], 99 );
```

### Reprioritize Actions and Filters

```php
<?php
use function Alley\WP\reprioritize_action;
use function Alley\WP\reprioritize_filter;

reprioritize_action( 'init', [ '\Plugin_Main', 'init' ], 1, 10 );
reprioritize_filter( 'the_content', [ '\Plugin_Main', 'the_content' ], 99, 20 );
```

### Retrieve an object instance

Here's a fictituous example using the above plugin code. That code uses dependency injection to pass a file-based logger to the `Plugin_Main` class. Say we want to log to Redis instead of a file, it looks like we should be able to replace the dependency using the `set_logger()` method -- however, we can't do that because we don't have access to the instance of `Plugin_Main`. Below, we leverage `get_hooked_object` to retrieve the plugin instance and then call the `set_logger` method on it to replace the file logger with a redis logger.

```php
<?php
use function Alley\WP\get_hooked_object;

$plugin = get_hooked_object( 'init', [ '\Plugin_Main', 'init' ], 1 );
$plugin->set_logger( new \Redis_Logger() );
```

### License

[GPL-2.0-or-later](https://github.com/alleyinteractive/wp-captain-hook/blob/main/LICENSE)

### Maintainers

[Alley Interactive](https://github.com/alleyinteractive)
