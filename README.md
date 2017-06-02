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

## Run the Unit Tests

```
$ make test
```