# Flow for laravel

Flow

- The flow is responsible for the executin the action in the correct time and order.

Actions

- must have a handle() function, where any set of parameters are possible and any return value is possible.
- can be queued, wont have a result value
- if an authorize() method is present it will be called in the FlowServiceProvider after it is resolved.

```php
$flow = new Flow();

$flow
    //->runAs($user)             // for when queued
    //->wrapInTransaction()
    //->logLevel(LogLevel::debug)
    ->authorize()
    ->action(ActionOne::class)
    ->action(ActionTwo::class, ['staticInput' => 'hello world', 'dynamicBeforeFlowDefinition' => User::first()])
    ->action(
        ActionThree::class,
        fn(Flow $f) => 'ResponseFromPreviousStep' => $flow->getResult(ActionTwo::class)
    );

$flow->run(); // $flow->queue(); $flow->schedule(Carbon::now()->addday())

return $flow->getResult(ActionThree:class);
```

run will loop all added actions, if the 2nd param is a closure will then be executed. that way the result of a previous step is retrievable from the Flow instance.
