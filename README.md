# IG API Scrapper PHP

![Demo GIF](/demo-compressed.gif)

## Description

Get the last _n_ posts from your Instagram account and download them into your server, so you don't need to call the API every time a user requests the page, and call a local file instead.

We only provide access to:

- Post image
- Post URL

The reason for this is that the "Instagram Basic Display API" can only get _basic_ data.

#### Why this exists

Because seems that Facebook's Instagram is blocking/black-listing IPs that are making recurrent calls to their `?__a=1` public endpoint. So this is kinda a workaround for that.

## Requirements

This software/script is build on top of the Facebook's "Instagram Basic Display" and it needs [instagram-basic-display-php](https://github.com/espresso-dev/instagram-basic-display-php) (and its requirements) to work.

- Instagram User Token (both)
- PHP 7 or higher (both*)
- cURL (only _instagram-basic-display-php_)
- PHP's `file_get_contents()` enabled (only _ig-api-scrapper-php_)

You can install _instagram-basic-display-php_ using [Composer](https://getcomposer.org/):

`$ composer require espresso-dev/instagram-basic-display-php`

#### "Instagram User Token"?

**We assume you already have an "Instagram User Token"**, if you don't have one and don't know where find it, you can follow [this tutorial](https://github.com/jimmyadaro/ig-api-scrapper-php/wiki/How-to-get-the-Instagram-User-Token) from the project's Wiki page.

---

\* Even if this script doesn't _need_ PHP 7+ –since we use functions available in 5.x–, it's recommended to use 7+ because it's awesome.

## How it works

This script will connect to your Instagram account using the Instagram User Token, get the last _n_ posts (read "_Configuration_"), check if the last one is already inside the "dist" folder (read "_Installation_") and if it is no there will download the last _n_ posts in the "tmp" folder, and then replace the "dist" folder content –erasing everything inside that directory!– with the new content from "tmp".

This way we provide **near-zero downtime**, which may vary depending on how fast your system is for moving files from one folder to another (which usually is a quite light task).

The downloaded images have this format: `<POST_NUMBER>-<URL_CODE>-.jpg` So, if you download your last 6 posts, the last file will be named something like: `6-abc123.jpg` We include a **simple example** of how to get this posts and their URL in the `index.php` file of this repository, which will be **empty until you execute the script** provided in the section "_Try it_" of this document.

## Installation

You just need to place both `ig-cronjob.php` and `ig-cronjob.ini` in your project's root folder.

Then you should create the required folder structure:

```
your-site/
└── assets/
   └── ig/
       ├── dist/
       ├── log/
       └── tmp/
```

You can create those using `mkdir`

Replace `/path/to/ig-api-scrapper-php/` with your own `/path/to/your-site/`

```
$ mkdir -p /path/to/ig-api-scrapper-php/assets/ig/{dist,log,tmp}
```

## Configuration

You need to **set the number of posts you want to get** from your feed (by default it gets 6) and the **Instagram User Token** (see how to get it [here](https://github.com/jimmyadaro/ig-api-scrapper-php/wiki/How-to-get-the-Instagram-User-Token)). This can be done renaming or copying the provided `ig-cronjob-sample.ini` file to `ig-cronjob.ini`

Replace `/path/to/ig-api-scrapper-php/` with your own `/path/to/your-site/`

```
# Copy it
$ cp /path/to/ig-api-scrapper-php/ig-cronjob-sample.ini /path/to/ig-api-scrapper-php/ig-cronjob.ini

# Or rename it
$ mv /path/to/ig-api-scrapper-php/ig-cronjob-sample.ini /path/to/ig-api-scrapper-php/ig-cronjob.ini
```

In that `ig-cronjob.ini` file you should add your token to `fb_app_token` and set the number of photos you want to get in `post_to_get` (both inside the quotation marks).

**Example:**

```
[config]
fb_app_token = "abc123"
post_to_get = "3"
```

You can get **up to 99 posts** from the API.

### IMPORTANT

You should **_NEVER_** commit the `ig-cronjob.ini` file to any [public] version control system (e.g. _Github_, _Gitlab_, whatever) since **it includes the Instagram User Token** of the linked Instagram account. **That is a private and secret token** and should not be shared to any external developers. To be sure, just add the `ig-cronjob.ini` to your `.gitignore` file (as well as the `log` folder).

Is **highly recommended** that you block the access to any `.ini` and `.log` file in your server (if you don't already) so it cannot be used outside your server and cannot be seen entering the URL in the browser.

If you use Apache you can set it like this:

```
# Inside your .htaccess file
<FilesMatch "\.(ini|log)$|(\.*~)$">
  Order Allow,Deny
  Deny from all
</FilesMatch>
```

If you use nginx you can set it like this:

```
# Inside your nginx config
location ~* (\.ini|\.log)$ {
  deny all;
  error_page 403 =404 / ;
}
```

## Try it

You can get the first batch of posts using the script below, just to try how it works. It'll log any action in its own `cron.log` file.

Replace `/path/to/php` with your path to PHP (usually `/usr/bin/php` – you can get that path using `which php` in your terminal), and replace `/path/to/ig-api-scrapper-php/` with your own `/path/to/your-site/`

```
$ /path/to/php /path/to/ig-api-scrapper-php/ig-cronjob.php >> /path/to/ig-api-scrapper-php/assets/ig/log/cron.log 2>&1
```

The `cron.log` file will be something like this:

```html/text
[2020-06-19T04:20:10-03:00] [INFO] Starting download...
[2020-06-19T04:20:11-03:00] [INFO] Downloaded post #1: 1-B9wLmirAyhi.jpg
[2020-06-19T04:20:11-03:00] [INFO] Downloaded post #2: 2-B9kSpuugyFq.jpg
[2020-06-19T04:20:11-03:00] [INFO] Downloaded post #3: 3-B9e0E7iAMGh.jpg
[2020-06-19T04:20:12-03:00] [INFO] Downloaded post #4: 4-B9Z49MKAQD9.jpg
[2020-06-19T04:20:12-03:00] [INFO] Downloaded post #5: 5-B9JvSOxAYPl.jpg
[2020-06-19T04:20:12-03:00] [INFO] Downloaded post #6: 6-B82p3t3gAtR.jpg
[2020-06-19T04:20:12-03:00] [OK] Successfully downloaded last 6 posts
```

#### Possible errors:

- The config file (`ig-cronjob.ini`) was not found or is not accessible by PHP. It is expected to be found in the same root folder as the `ig-cronjob.php` file.

```html/text
[2020-06-19T04:20:05-03:00] [ERROR] Config file was not found, execution failed
```

## Cronjob & API's rate limits (quota)

The script works like a charm with a cronjob (as it's intended to be used, as you may have seen in the filenames).

Currently Facebook allows **up to 240 calls per hour** –per user, but this script only uses 1 user– to the "Basic Display API". This is probably more than we need if we're not really _that active_ on Instagram, so any cronjob like the one provided below would be enough.

Replace `/path/to/php` with your path to PHP (usually `/usr/bin/php` – you can get that path using `which php` in your terminal), and replace `/path/to/ig-api-scrapper-php/` with your own `/path/to/your-site/`

```
*/10 * * * * /path/to/php /path/to/ig-api-scrapper-php/ig-cronjob.php >> /path/to/ig-api-scrapper-php/assets/ig/log/cron.log 2>&1
```

This will check the current posts –and download the newer if needed– every 10 minutes, using **just 6 calls per hour** of our quota.

If you need to edit how often this cronjob is called, I recommend [Crontab Guru](https://crontab.guru/#*/10_*_*_*_*) to get a better understanding in schedule expressions.

## To-do

- Improve log system for main execution file
- Extend access to more API data, such as "captions" and "timestamp"
- Make it object-oriented
- Check for errors and return Exceptions
