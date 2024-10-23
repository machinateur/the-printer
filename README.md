# machinateur/the-printer

The printer, a prototype. Create PDF documents and images from HTML content using puppeteer and headless-chrome.

This repository contains the express app (node) and a PHP client implementation with stream support.

The PHP SDK has zero package dependencies, to keep it as simple as possible.

## Requirements

- At least PHP `>=8.1` is required
  - Extensions `ext-json` and `ext-curl` are reqired
- Node LTS `18` is recommended
- Use NPM or Yarn
- Chrome (headless)

## Installation

```
composer require machinateur/the-printer
npm i --save @machinateur/the-printer
```

Note that the express app is also bundled with the composer package.

## Advantages

- Support, not only for `pdf` documents, but also images (`png`, `jpeg`, `webp`)
- Design advanced PDF layouts using modern web technologies (HTML + CSS)
  - All the latest features, like `flex` etc., are available
- Preview design directly in chrome-based browsers `document.print()` dialog
  - No need for lengthy feedback and testing loops
- Large number of options available ([see "Supported endoints"](#supported-endpoints))
  - Full control over the printing process result
- JavaScript support (images only)
  - _You've been warned_ :)
- Integrate fonts in various formats without the hardships (i.e. font-caches, loading delay, etc.)
  - The printing process will wait for fonts to load by default
  - All the well-known formats supported by chrome are available
- Full test coverage
  - The test-suite included with this library is well-procured
- Documentation
  - Many documentation resources are available
  - The code self-contains documentation (can be used to [generate an API reference](#generating-phpdoc))

## Usage

### Running the express app

Run

```bash
npm start
```

 or

```bash
node the-printer.js
```

 to launch the express app. Make sure to install the dependencies first.

#### Supported CLI args and environment variables

The following arguments are supported, with respective fallback to the env-var.

- Option `--debug`: Control debug output and `dumpio` from chrome `STDOUT`/`STDERR`.
  - Fallback: `APP_DEBUG`
  - Default: `false`
- Option `--connect-to`: Use an existing browser instance at that address, supports `ws` and `http`.
  - Fallback: `APP_REMOTE`
  - Default: Launch headless chrome instance locally.
- Option `--script`: Control JavaScript execution.
  - Fallback: `APP_ENABLE_JAVASCRIPT`
  - Default: `false` (JavaScript is not executed)
- Option `--port`: Set the app port, format `\d+`.
  - Fallback: `APP_PORT`
  - Default: `3000`

### Calling from PHP

From PHP the bundled client provided in this composer package may be used to interact with the
 express app. It has to be running for this to work.

#### Create and return a document from php

The following example will render a document (in `A5` format without header/footer)
 and return it directly to the output buffer.

```php
$configuration = new \Machinateur\ThePrinter\Configuration\DocumentConfiguration();
$configuration->setPageFormat('A5');

$content = '<!-- The HTML document content goes here. -->';

$outputBuffer = \fopen('php://output', 'w', false, null);

$client = new \Machinateur\ThePrinter\Client('http://127.0.0.1:3000/', 10);

\header(\sprintf('%s: %s', 'Content-Type', 'application/pdf'), true, 200);

$client->document($configuration, $content, $outputBuffer);
```

You can use the `\Machinateur\ThePrinter\Client::documentBinary()` method instead, to get the output as a binary string.
It's always possible to write to a stream resource and process/read it later, to save memory.
If no buffer is provided, it is created as temporary stream and has to be closed by the implementor.
The PDF documents do not support JavaScript execution before rendering. This is a engine limitation.

#### Create and return an image from php

The following example does about the same thing, but with a (full-page) screenshot.

```php
$configuration = new \Machinateur\ThePrinter\Configuration\ImageConfiguration();
$configuration->setCapturePage(true);

$content = '<!-- The HTML document content goes here. -->';

$outputBuffer = \fopen('php://output', 'w', false, null);

$client = new \Machinateur\ThePrinter\Client('http://127.0.0.1:3000/', 10);

\header(\sprintf('%s: %s', 'Content-Type', 'image/png'), true, 200);

$client->image($configuration, $content, $outputBuffer);
```

As with the `\Machinateur\ThePrinter\Client::document()` method, there is
 a `\Machinateur\ThePrinter\Client::imageBinary()` method to retrieve the buffer as string blob.
The provided HTML content is set as the page content directly. Keep in mind that JavaScript is disabled
 in case you rely on it for rendering. That setting can be controlled from the `--no-script` option
 or using the `APP_ENABLE_JAVASCRIPT` env-var.

### Calling from other languages

It's totally possible to interact with the express app from other programming languages as well.
Just use the [supported endpoints](#supported-endpoints) directly via your favourite HTTP client.
The response content will be a binary stream of the mime-type from the response header.
In case of an error (request format or something else) the response will be a json object of
 type `{Error&{statusCode?:number}}` where `trace` is removed when not in debug mode.

### Using  HTTP files

Find details on the available endpoints and their options in the HTTP files contained in this repository.

> TODO

## Supported endpoints

> **`POST` `/document`**
>
> Create a PDF document from the provided content string.
>
> ```json
> {
>   "configuration": {
>     ...
>   },
>   "content": "<!-- The HTML document content goes here. -->",
>   "time": 1696091198
> }
> ```
>
> Configuration:
>
> ```js
> /**
>  * @typedef {object} DocumentConfiguration
>  * @property {number|null} scale
>  * @property {boolean} displayContentOnly
>  * @property {boolean} displayBackgroundGraphic
>  * @property {boolean} displayTransparent
>  * @property {object|null} template
>  * @property {string|null} template.header
>  * @property {string|null} template.footer
>  * @property {string} pageOrientation
>  * @property {string|null} pageFormat
>  * @property {string|number|undefined} pageWidth
>  * @property {string|number|undefined} pageHeight
>  * @property {string} pageRange
>  * @property {boolean} pageOverride
>  * @property {object|null} margin
>  * @property {string|number|undefined} margin.top
>  * @property {string|number|undefined} margin.right
>  * @property {string|number|undefined} margin.bottom
>  * @property {string|number|undefined} margin.left
>  */
> ```
>
> Further reference:
>
> - https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.pdfoptions.md
> - https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.pdfmargin.md
> - https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.paperformat.md

> **`POST` `/image`**
>
> Create an image from the provided content string.
>
> ```json
> {
>   "configuration": {
>     ...
>   },
>   "content": "<!-- The HTML document content goes here. -->",
>   "time": 1696091198
> }
> ```
>
> Configuration:
>
> ```js
> /**
>  * @typedef {object} ImageConfiguration
>  * @property {string|null} type
>  * @property {number|null} quality
>  * @property {number|null} scale
>  * @property {object|null} area
>  * @property {number} area.x
>  * @property {number} area.y
>  * @property {number} area.width
>  * @property {number} area.height
>  * @property {boolean} optimize
>  * @property {boolean} captureViewportOnly
>  * @property {boolean} captureSurface
>  * @property {boolean} capturePage
>  * @property {boolean} displayTransparent
>  */
> ```
>
> Further reference:
>
> - https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.screenshotoptions.md
> - https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.screenshotclip.md

## Development

### Setup

To set this project up from scratch for development, run the following commands:

```bash
git clone git@github.com:machinateur/the-printer.git
cd the-printer
composer install
```

### Running tests

Execute the PHPUnit test-suite using the following command:

```bash
composer run-script --timeout=0 tests
```

All tests are stored at `./tests` under the `\Machinateur\ThePrinter\Tests\...` namespace.

The configuration is stored in [`phpunit.xml.dist`](phpunit.xml.dist).

#### Running tests in multiple PHP versions

It's possible to run the tests in different versions of PHP without the need to install all those versions locally.
 Thanks to docker, this can be done using a single command:

```bash
PHP_VERSION=8.1

docker run --rm -v "$(pwd):/app" -w /app 'composer'           install
docker run --rm -v "$(pwd):/app" -w /app "php:${PHP_VERSION}" vendor/bin/phpunit
```

See [this guide](https://www.shiphp.com/blog/testing-multiple-versions-of-php) for some more details.

#### Generate coverage

Execute the PHPUnit test-suite and gain coverage insights using the following command:

```bash
composer run-script --timeout=0 coverage
```

This command uses the text UI of PHPUnit exclusively.

#### About `ProcessTestCase`

The class `\Machinateur\ThePrinter\Tests\ProcessTestCase` is a custom `\PHPUnit\Framework\TestCase` that supports
 managing a background process that is started and stopped with the _test class_ itself.

While this can impact the test-suite performance quite heavily, if used excessively
 and depending on the type of background process.

This is utilized to enable a functional test using `node` as background process running the `the-printer.js` server.
 The test is located at `` and holds some other interesting details, discussed [below](#embedded-phpt-test-suite).

#### About `ServerTestCase`

The class `\Machinateur\ThePrinter\Tests\Server\ServerTestCase` is a variant of the `ProcessTestCase` that specifically uses
 the PHP built-in webserver. This can be useful, if a test requires a _mocked webserver_.

This is utilized to mock server responses for actual testing of the `curl` calls
 within the `\Machinateur\ThePrinter\Stream\Connection`.

#### Embedded PHPT test-suite

The project-level test-suite embeds a separate test-suite as part of the `\Machinateur\ThePrinter\Tests\Result\ResultTest`,
 where some separate [PHPT tests](https://qa.php.net/phpt_details.php) are loaded.

You can find other examples of such tests being used,
 for example in the [PHP `curl` extension](https://github.com/racklin/curl_ext_52x/blob/master/tests/curl_CURLOPT_READDATA.phpt)
 itself.

Thanks to [PHPUnit's built-in support](https://github.com/sebastianbergmann/phpunit/blob/10.5/src/Runner/PhptTestCase.php)
 for the ancient PHPT format, although somewhat thinly documented, they were really easy to integrate.

The PHPT tests are used to test the accuracy of actual printing results (image and document) using imagick.
This comes with some drawbacks and was an experimental approach in the beginning. It can serve as an example for
 real world use-cases of this "digital print server".

Feel free to [browse the code](tests/Result) and have a look yourself. Improvements and creative additions are always
 welcomed.

#### Managing control files

The comparison (aka. control) files for PHPT tests are stored under `tests/Result/res/`.

### Running static analysis

To execute PhpStan for static analysis, call the following composer script:

```bash
composer run-script lint
```

This command maps to `./vendor/bin/phpstan analyze` using the [`phpstan.neon`](phpstan.neon) file from the project root.

### Generating `phpdoc`

It's possible to generate `phpdoc` with [phpDocumentor](https://docs.phpdoc.org/guide/getting-started/installing.html#installation):

```bash
rm -r docs/ var/phpdoc/
docker run --rm -v "$(pwd):/data" 'phpdoc/phpdoc:3'
```

The resulting documentation will be placed in `docs/`, the case is located at `var/phpdoc/`, both are ignored from git.

## License

It's MIT.
