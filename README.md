# NewsSettings

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL"
in this document are to be interpreted as described in
[RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**

* [Requirements](#requirements)
* [Installation](#installation)
  * [Composer](#composer)
* [Configuration](#configuration)
* [Specifications](#specifications)
* [Other information](#other-information)
  * [Correlations](#correlations)
  * [Bugs](#bugs)
  * [Funding](#funding)
  * [License](#license)

## Requirements

* PHP: [![Minimum PHP Version](https://img.shields.io/badge/Minimum_PHP-8.1.x-blue.svg)](https://php.net/) [![Maximum PHP Version](https://img.shields.io/badge/Maximum_PHP-8.2.x-blue.svg)](https://php.net/)
* ILIAS: [![Minimum ILIAS Version](https://img.shields.io/badge/Minimum_ILIAS-9.0-orange.svg)](https://ilias.de/) [![Maximum ILIAS Version](https://img.shields.io/badge/Maximum_ILIAS-9.999-orange.svg)](https://ilias.de/)

## Installation

This plugin MUST be installed as a EventHook Plugin.

	<ILIAS>/Customizing/global/plugins/Services/EventHandling/EventHook/NewsSettings

Correct file and folder permissions MUST be
ensured by the responsible system administrator.

### Composer

After the plugin files have been installed as described above,
please install the [`composer`](https://getcomposer.org/) dependencies:

```bash
cd Customizing/global/plugins/Services/EventHandling/EventHook/NewsSettings
composer install --no-dev
```

Developers MUST omit the `--no-dev` argument.

## Configuration

None

## Specifications

An ILIAS plugin that applies defaults to news settings of new objects and provides
a user interface for news setting migrations.

## Other Information

### Correlations

None

### Bugs

None

### Funding

* Co-Funded for ILIAS 6 & 7 by Katholische Hochschule NRW, Bundesarbeitsgemeinschaft eLearning und Universität Konstanz
* ILIAS 8-Update funded by: Katholische Hochschule NRW

### License

See [LICENSE](./LICENSE) file in this repository.
