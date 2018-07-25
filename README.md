# idp-pw-api-personnel-idbroker
IdP Password Management personnel component for ID Broker 

# Summary
This project has one class (*IdBroker*) with three public methods which 
use the ID Broker php client to get person data from the ID Broker service.
Each of these functions attaches that data to a PersonnelUser instance
which it then returns.

The public methods are ...

  * findByEmployeeId($employeeId)
  * findByUsername($username) // Not supported by ID Broker so just throws exception, but is required by interface
  * findByEmail($email) // Not supported by ID Broker so just throws exception, but is required by interface

## Configuration
This code is loaded in as a Yii2 Component in the main config file. Here is an example:

```php
'components' => [
    'personnel' => [
        'class' => 'Sil\IdpPw\Common\Personnel\IdBroker\IdBroker',
        'baseUrl' => Env::requireEnv('ID_BROKER_BASE_URI'),
        'accessToken' => Env::requireEnv('ID_BROKER_ACCESS_TOKEN'),
        'assertValidBrokerIp' => true,
        'validIpRanges' => ['10.0.01/16','127.0.0.1/32'],
    ],
]
```

A more concise example:

```php
'components' => [
    'personnel' => ArrayHelper::merge(
        ['class' => 'Sil\IdpPw\Common\Personnel\IdBroker\IdBroker'],
        Env::getArrayFromPrefix('ID_BROKER_')
    ),
]
```

## Run the Unit Tests

```
$ docker-compose run --rm test /data/run-tests.sh
```
