Yelly is a PHP library that converts Markdown files into html files so that you can easilly deploy to Github page or similar platform. I write it for my personal blog.

## Install
1. You need to have PHP Cli installed
2. Clone or download this repository

## How to use
1. put markdown files into `input` directory
2. `php src/Yelly.php` will generate html files and store them in `output` directory. Copy files to remote host and you are done!
3. It is hightly recomanded to write a bash script to make it easier to deploy.
