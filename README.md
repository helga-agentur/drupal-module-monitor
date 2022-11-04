# Monitor
The Joinbox Monitor tool consists of the following two parts.
- [Monitor](https://github.com/joinbox/d9-module-monitor)
- [Instance](https://github.com/joinbox/d9-module-monitor-instance)

## Installation Instance
As soon as you put the following lines in your settings.php file, monitoring functionality is enabled. The instance sends information to your monitor, each time the cron job is run.

```php
$settings['instance'] = [
  'monitor' => 'https://www.joinbox.com',
  'user' => 'admin',
  'password' => 'admin',
];
```
