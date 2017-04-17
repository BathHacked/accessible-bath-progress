
# Accessible Bath Progress Tracker

## Requirements

- Composer
- PHP >= 5.5.0
- PDO compatible database

## Installation

Clone this repository and point your server at the ``public_html`` directory of the project. 
An ``.htaccess`` file is provided for Apache.

Run ``composer install`` to install dependencies. 

Ensure the ``storage`` directory has write permission for your web server.

## Configuration

Copy ``config/db/settings.example.php`` to ``config/db/settings.php`` and 
edit to reflect your database setup.

Copy ``config/wheelmap.example.php`` to ``config/wheelmap.php`` and edit to include your
Wheelmap API key (sign up at [Wheelmap](https://wheelmap.org) 
and visit [your profile](https://wheelmap.org/en/profile/edit) to get a key).
 
__Make sure you specify a bounding box for the area you wish to track__.


Errors will be logged to ``storage/logs/app.log`` for web app errors 
and ``storage/logs/jobs.log`` for batch job errors. 
If you'd like to log messages to a Slack webhook edit ``config/logger.php``. 
  
Display of error messages in the browser is turned off by default. To enable it change ``displayErrorDetails`` in
``config/slim/settings.php`` to ``true``. This will also disable caching of Twig templates.

## Data Setup

Set up your database using ``migrate/setup.sql`` or ``php bin/migrate.php``.

Run ``php bin/update.php`` to initially populate your database. This will fetch data about 
Wheelmap locations, categories and types and also fetch data from OpenStreetMap about 
the history of those locations. 

For best results, try to limit your bounding box to encompass less than 5,000 locations.

## Data update

To periodically update the data use ``php bin/update.php``.

## Customising

Obviously, you'll want to change logos and all text throughout the app.

## Live Example

A live example website can be viewed at [Accessible Bath Progress](https://accessiblebath.org).

## License

The MIT License (MIT)

Copyright (c) Bath Hacked

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
