## Soupmix Redis Cache Adaptor

[![Build Status](https://travis-ci.org/soupmix/cache-redis.svg?branch=master)](https://travis-ci.org/soupmix/cache-redis) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/soupmix/cache-redis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/soupmix/cache-redis/?branch=master) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/f2fd85aaddc44793bfc25020802ee5f2)](https://www.codacy.com/app/mehmet/cache-redis?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=soupmix/cache-redis&amp;utm_campaign=Badge_Grade) [![Code Climate](https://codeclimate.com/github/soupmix/cache-redis/badges/gpa.svg)](https://codeclimate.com/github/soupmix/cache-redis) 
[![Latest Stable Version](https://poser.pugx.org/soupmix/cache-redis/v/stable)](https://packagist.org/packages/soupmix/cache-redis) [![Total Downloads](https://poser.pugx.org/soupmix/cache-redis/downloads)](https://packagist.org/packages/soupmix/cache-redis) [![Latest Unstable Version](https://poser.pugx.org/soupmix/cache-redis/v/unstable)](https://packagist.org/packages/soupmix/cache-redis) [![License](https://poser.pugx.org/soupmix/cache-redis/license)](https://packagist.org/packages/soupmix/cache-redis) [![composer.lock](https://poser.pugx.org/soupmix/cache-redis/composerlock)](https://packagist.org/packages/soupmix/cache-redis) [![Code Coverage](https://scrutinizer-ci.com/g/soupmix/cache-redis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/soupmix/cache-redis/?branch=master)

### Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Soupmix Cache Redis Adaptor.

```bash
$ composer require soupmix/cache-redis "~0.1.4"
```

### Connection
```
require_once '/path/to/composer/vendor/autoload.php';

$rConfig = [];
$rConfig['host'] = "127.0.0.1";
$rConfig['dbIndex'] = 1;
$cache = new Soupmix\Cache\RedisCache($rConfig);
```

### Soupmix Redis Cache API

[See Soupmix Cache API](https://github.com/soupmix/cache-base/blob/master/README.md)
