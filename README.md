# Monitor
The Helga monitor tooling consists of the following two parts.
- [Monitor](https://github.com/helga-agentur/drupal-module-monitor)
- [Instance](https://github.com/helga-agentur/drupal-module-monitor-instance)

## Installation
- add coralogix api credentials to your settings.php
- install monitor

## Functionality
The monitor has two endpoints.
- The Log-Endpoint:
  - This one receives logs from all instances and passes them over to coralogix.
- The Instance-Endpoint:
  - This one receives instance data like branch, drupal version etc. and stores them into a queue.
    With every cron run the queue will be processed.
- Consider to add your cron-url into a serverside crontab. Because if your monitor is a standalone installation, the drupal cron is only triggered if you visit the monitor and your queue could be very large if you don't do this regularly.

## Settings
There are several settings you can change.
- **Dummy data**: Visit `/admin/monitor` to enable reading from dummy data interface (data.json)
- **Manage storage**:  Visit `/admin/monitor/storage` to delete environments or projects you don't need anymore
