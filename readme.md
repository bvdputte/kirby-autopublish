# Kirby autopublish plugin

Kirby 3 plugin to schedule the automatic publishing of pages (drafts) on a certain date+time.
It is built to work with enabled cache.

## Installation

- unzip [master.zip](https://github.com/bvdputte/kirby-autopublish/archive/master.zip) as folder `site/plugins/kirby-autopublish` or
- `git submodule add https://github.com/bvdputte/kirby-autopublish.git site/plugins/kirby-autopublish` or
- `composer require bvdputte/kirby-autopublish`

⚠️ Highly recommended to also install the [kirby-log plugin](https://github.com/bvdputte/kirby-log), to get page-publication logs.

## Usage

### Setup worker

#### 1. Via Cron

Add the worker file `site/plugins/kirby-autopublish/worker.php` to [cron](https://en.wikipedia.org/wiki/Cron) or similar at the desired interval (.e.g. each minute).

💡 This is the preferred method for setting up kirby-autopublish.

#### 2. Poor man's cron

When cron is not installed on your server, you can _fake_ cron by [enabling this](#options-and-opinionated-defaults) in `config.php`.

### Setup field in blueprint

```yaml
autopublish:
  label: Autopublish on
  type: date
  time: true
  default: now
```

## Options and opinionated defaults

Set in `config.php`:

### Field name

Autopublish searches for a date-field. By default the name is `autopublish`, but can be changed:

```php
'fieldName' => 'myautopublishfieldname'
```

### Poor man's cron

By default, this is disabled. Enable:

```php
// Enable poor man's cron
'bvdputte.kirbyAutopublish.poormanscron' => true
```

The default interval for poor man's cron to check is each minute. Change this to e.g. quarterly:

```php
'bvdputte.kirbyAutopublish.poormanscron.interval' => 15
```

### Webhook

You can also configure autopublish to run via a webhook:

```php
'bvdputte.kirbyAutopublish.webhookToken' => 'my-secret-token'
```

Now you can trigger the autopublish check via `https://mydomain.com/kirby-autopublish/my-secret-token`.

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bvdputte/kirby-queue/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
