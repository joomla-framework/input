# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement and removes the legacy `Cookie::set()` signature that had
been deprecated since 1.4.0.

## At a glance

| | v3 (3.0.2) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| `Cookie::set()` positional signature | deprecated, works | **removed** |
| `joomla/filter` | `^3.0` | `^4.0` |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## The legacy `Cookie::set()` signature was removed

Before 1.4.0 the method took the cookie attributes as positional arguments. 1.4.0 added the
options-array form and kept a compatibility layer that inspected `func_get_args()`; 4.0.0 removes
that layer.

```php
// Removed in 4.0.0 - the positional form
$cookie->set('lang', 'de', time() + 86400, '/', 'example.com', true, true);

// The options form, available since 1.4.0
$cookie->set('lang', 'de', [
    'expires'  => time() + 86400,
    'path'     => '/',
    'domain'   => 'example.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

Passing anything other than an array as the third argument now reaches `setcookie()` unchanged and
raises a `TypeError` there rather than being translated.

The options form supports `samesite`, which the positional form never could — worth setting while
you are changing these call sites anyway.

To find them:

```bash
grep -rn -- '->set(' src/ | grep -i cookie
```

## No other API changes

Apart from the removed compatibility layer, `Input`, `Cookie`, `Files` and `Json` are unchanged.

## Dependency changes

| Package | v3 (3.0.2) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |
| `joomla/filter` | `^3.0` | `^4.0` |
| `symfony/deprecation-contracts` | `^2 \| ^3` | unchanged |
