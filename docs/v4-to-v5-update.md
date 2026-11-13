# Updating from v4 to v5

The following changes were made to the Input package between v4 and v5.

## Native parameter, return and property types

`Input`, `Cookie`, `Files` and `Json` now declare native types instead of relying on docblocks:

| Method / property | v5 signature |
|---|---|
| `__construct()` (all classes) | `__construct(?array $source = null, array $options = [])` |
| `Input::__get()` | `__get(string $name): Input` |
| `Input::count()` | `count(): int` (the `#[\ReturnTypeWillChange]` attribute is gone) |
| `Input::get()`, `Files::get()` | `get(string $name, mixed $default = null, $filter = 'cmd'): mixed` |
| `Input::getArray()` | `getArray(array $vars = [], mixed $datasource = null): array` |
| `Input::getInputForRequestMethod()` | `getInputForRequestMethod(): Input` |
| `Input::set()`, `Files::set()` | `set(string $name, mixed $value)` |
| `Cookie::set()` | `set(string $name, mixed $value, array $options = [])` |
| `Input::def()` | `def(string $name, mixed $value): void` |
| `Input::exists()` | `exists(string $name): bool` |
| `Input::__call()` | `__call(string $name, array $arguments): mixed` |
| `Input::getMethod()` | `getMethod(): string` |
| `Files::decodeData()` | `decodeData(array $data): array` |
| `Input::$options`, `$data`, `$inputs` | `protected array` |
| `Input::$filter` | `protected Joomla\Filter\InputFilter` |
| `Files::$decodedData` | `protected array` |

What to check:

* **Subclasses** that override any of these methods must declare compatible signatures, e.g.
  `public function get(string $name, mixed $default = null, $filter = 'cmd'): mixed`.
* **The `filter` option** must be an `InputFilter` (or a subclass such as the CMS's); other objects
  with a `clean()` method are no longer accepted.
* **`$source`** must be an array or `null`.
* Names are `string`. Integer keys still work, as PHP converts them in non-strict mode.

`set()` keeps an untyped return value because `Cookie::set()` returns whether the cookie was sent.
