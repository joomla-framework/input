# Overview

The Input package is the request-side counterpart to the application's response: it wraps the
superglobals and returns filtered values through a single API.

```bash
composer require joomla/input
```

## What is in the package

| Class | Source | Purpose |
|---|---|---|
| `Joomla\Input\Input` | `$_REQUEST` | The base class; also the type of `$input->get`, `$input->post`, … |
| `Joomla\Input\Cookie` | `$_COOKIE` | Adds `set()` writing a real cookie |
| `Joomla\Input\Files` | `$_FILES` | Pivots the upload array into per-file entries |
| `Joomla\Input\Json` | `php://input` | Decodes a JSON request body |

## Reading values

Every read goes through a filter. The default is `cmd`:

```php
use Joomla\Input\Input;

$input = new Input();

$input->get('name');                    // filtered with 'cmd'
$input->get('name', 'anonymous');       // with a default
$input->get('body', '', 'raw');         // unfiltered
```

The `get<Type>()` shorthands are resolved through `__call()`, so the type name is the filter name:

| Call | Filter |
|---|---|
| `getInt()`, `getUint()`, `getFloat()` | numeric |
| `getBool()` | boolean |
| `getWord()`, `getAlnum()`, `getCmd()` | restricted character sets |
| `getBase64()` | base64 alphabet |
| `getString()`, `getHtml()` | sanitised text |
| `getPath()` | file path |
| `getUsername()` | control characters and `<>"'%&` removed |
| `getRaw()` | nothing |

Anything else — `getFoobar()` — silently falls back to string filtering rather than raising an
error, so a typo in the method name returns a value instead of failing.

## The sub-inputs

Magic properties give access to the other superglobals, each wrapped in its own `Input`:

```php
$input->get->getInt('page');        // $_GET
$input->post->getString('title');   // $_POST
$input->server->getString('REQUEST_METHOD');
$input->env->getString('PATH');
$input->cookie->getString('lang');  // Joomla\Input\Cookie
$input->files->get('upload');       // Joomla\Input\Files
$input->json->getString('name');    // Joomla\Input\Json
```

Note that `isset($input->post)` returns `false` — the class implements `__get()` but not
`__isset()`.

## Writing values

```php
$input->set('view', 'articles');       // overwrite
$input->def('layout', 'default');      // only if not already present
$input->exists('view');                // true
count($input);                         // Countable
```

These change the `Input` object only; the superglobals are untouched.

## Reading several values at once

```php
$data = $input->getArray([
    'id'    => 'uint',
    'title' => 'string',
    'tags'  => ['name' => 'string'],   // nested
]);
```

Called without arguments, `getArray()` returns everything — but be aware that it then uses each
*value* as the filter name for its own key, which is not what the docblock describes. Pass an
explicit map whenever the result matters.

`getArray()` also assumes nested keys exist: `getArray(['a' => ['b' => 'int']], $source)` raises a
warning and a `TypeError` if `$source['a']` is missing. Supply defaults or check first.

## Request method

```php
$input->getMethod();                    // 'GET', 'POST', …
$input->getInputForRequestMethod();     // the $_GET or $_POST input
```

For `PUT`, `PATCH` and `DELETE` there is no superglobal, so `getInputForRequestMethod()` returns
the `$_REQUEST`-backed instance — which means the request body of those methods is **not read at
all**. Use `Joomla\Input\Json` or parse `php://input` yourself for them.

## Cookies

```php
use Joomla\Input\Cookie;

$cookies = new Cookie();

$cookies->set('lang', 'de', [
    'expires'  => time() + 86400,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

The options array is handed straight to `setcookie()`. Nothing is added, so **omitting `secure`,
`httponly` or `samesite` means the cookie is written without them**. Always pass them explicitly.

The return value of `setcookie()` is discarded, so a failed call — headers already sent, invalid
name — still updates the object's own data and reports nothing.

## Uploaded files

```php
$file = $input->files->get('avatar');

// ['name' => …, 'type' => …, 'tmp_name' => …, 'error' => …, 'size' => …]
```

Multi-file inputs are pivoted, so `files->get('attachments')` returns a list of such arrays rather
than PHP's column-wise structure. For directory uploads (`<input type="file" webkitdirectory>`),
PHP also sends the relative path of each file; it is returned as `full_path` when present.

A value that does not have the structure of a `$_FILES` entry — not an array, a missing `name`,
`type`, `tmp_name`, `error` or `size`, or a mix of lists and single values — is treated as absent,
so `get()` returns the default.

> **The filter argument is ignored.** `Files::get()` accepts a third `$filter` parameter and never
> applies it. The returned `name`, `type` and `full_path` are the raw, client-supplied values.
> Never build a path from `name` without cleaning it, and never trust `type` — validate the real
> MIME type with `finfo` instead.

`makeSafeFileName()` cleans a client-supplied file name for use in a path: it removes any directory
part, every character other than letters, digits, `.`, `_`, `-` and space, sequences of dots and
leading dots. The result may be empty.

```php
$file = $input->files->get('avatar');
$name = $input->files->makeSafeFileName($file['name']);   // '../../a.php' → 'a.php'
```

Uploaded files cannot be changed through the object: `Files::set()` and `Files::def()` do nothing.
Both are deprecated and will throw a `LogicException` in 5.0.

## JSON bodies

```php
use Joomla\Input\Json;

$json = new Json();

$json->getString('title');
$json->getRaw();              // the undecoded body
```

The body is read and decoded on construction regardless of the request's `Content-Type`. A body
that is not valid JSON yields an empty input rather than an error, so an API cannot distinguish
"malformed JSON" from "no fields sent" — check `getRaw()` if that matters.

## Not part of this package

* No PSR-7. There is no bridge from or to `ServerRequestInterface`.
* No `UploadedFileInterface`, no upload validation helpers (`UPLOAD_ERR_*`, size, MIME).
* No trusted-proxy handling for `X-Forwarded-For` or `X-Forwarded-Proto`.
* No `remove()`, no `ArrayAccess`, no `IteratorAggregate`.
