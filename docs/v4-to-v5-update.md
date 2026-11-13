# Updating from v4 to v5

The following changes were made to the Input package between v4 and v5.

## `Json::getRaw()` no longer returns the request body

`Json` declared its own `getRaw()` that returned the raw request body, which hid the
`getRaw($name, $default)` shorthand every other input has. The method is gone: `getRaw('field')`
now returns the unfiltered value of `field`, and the body is available from `getRawBody()` (added
in 4.x, where calling `getRaw()` without a name is deprecated).

```php
// v4
$body = $input->json->getRaw();

// v5
$body = $input->json->getRawBody();
```

Calling `getRaw()` without a name is an error, like any other `get<Filter>()` shorthand without a
name.
