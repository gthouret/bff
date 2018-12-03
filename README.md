# bff
###### A Light and fast PHP framework for getting things done

**Config** - Config management for multiple environments, production is base environment and other environments can override or add to production's config values

**Memcache** - Interact with Memcache a store

**TaggedMemcache** - Get and Set with tag sets; Invalidate caches by invalidating tags

**Registry** - A global, labelled, singleton object store

**Services** - Istantiates singleton services into the registry on demand; Simple clean interface for accessing from anywhere

**Text** - Useful text functions

**Time** - Useful Time constants and functions

## Example Usage

```php
use BFF\Services;

$user = [
    'name' => 'Joe Bloggs',
    'email' => 'joe@example.com'
];

$cache = Services::cache();
$cache->set('user-joebloggs', $user, Time::ONE_HOUR);
```
