# bff
###### A Light and fast PHP framework for getting things done

**Config** - Config management for multiple environments, production is base environment and other environments can override or add to production's config values

**Db** - Create custom MySQL connections via MysqliFactory and PdoFactory by passing your config reference or get a default connection via `Service::pdo()` or `Service::mysqli()`

**Memcache** - Interact with Memcache a store

**TaggedMemcache** - Get and Set with tag sets; Invalidate caches by invalidating tags

**Queue** - Queue implementation (currently only a Redis backend implementation)

**Registry** - A global, labelled, singleton object store

**Service** - Instantiates singleton services into the registry on demand; Simple clean interface for accessing from anywhere

**Export** - Export data (log, csv) to files via Redis queues (Diferent exports are defined in the exporters array)

**Logger** - Wraps Export to format log messages and send to the right exporter

**Text** - Useful text functions

**Time** - Useful Time constants and functions

## Example Usage

### Set an item to cache
```php
use BFF\Service;

$user = [
    'name' => 'Joe Bloggs',
    'email' => 'joe@example.com'
];

$cache = Service::cache();
$cache->set('user-joebloggs', $user, Time::ONE_HOUR);
```

### Load custom app services
```php
use BFF\Registry;
use BFF\Service as BffService;

namespace App;

class Service extends BffService {
    const MYSERVICEA = 'myservicea';
    const MYSERVICEB = 'myserviceb';
    
    public function myservicea() : MyServiceA
    {
        if (!Registry::isset(Service::MYSERVICEA)) {
            $obj = new MyServiceA();
            Registry::set(Service::MYSERVICEA, $obj);
            return $obj;
        } else {
            return Registry::get(static::MYSERVICEA);
        }
    }
    
    public function myserviceb() : MyServiceB
    {
        if (!Registry::isset(Service::MYSERVICEB)) {
            $obj = new MyServiceB();
            Registry::set(Service::MYSERVICEB, $obj);
            return $obj;
        } else {
            return Registry::get(static::MYSERVICEB);
        }
    }
}
```
```php
namespace App;

$myservicea = Service::myservicea();
$myservicea()->someAction();
```
