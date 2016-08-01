# NRS Gateway
NRS Gateway services integration.
This library allows you to use the SMS Push service from NRS Gateway.

## Quick Start
Just put in your username and password for the NRSGateway control panel, specify the recipients in array format and send it.
```php
use marqu3s\nrsgateway;

$nrs = new SMSService('username', 'password');
$nrs->to   = ['xxxxxxxxxxxxx', 'yyyyyyyyyyyyy'];
$nrs->from = 'Sender';
$nrs->msg  = 'This is a test message.';
$nrs->send();
```

