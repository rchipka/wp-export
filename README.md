# wp-export

Export WordPress users or posts tables to CSV

 * Exports WordPress table data as you see it in the admin view
 * Works with Admin Columns Pro table columns
 * Better than built-in Admin Columns Pro export functionality

## What makes it special

Most WordPress CSV table export plugins attempt to manually rebuild admin tables in CSV format, which causes issues with filtering, custom columns, and custom fields.

Instead, this plugin captures the HTML output of the actual admin table, sets `posts_per_page` to unlimited, sanitizes the columns, and converts the result to a CSV.

Whatever you see in the admin table is what you get in a CSV.

HTML will be gracefully downgraded to plain text.

## Supports

 * [x] custom post types
 * [x] custom columns
 * [x] sorted column views
 * [x] filtered column views
 * [x] filtered post status view
 * [x] users table
 * [x] unicode

## Installation

`include` or `require` wp-export.php and an *Export* button will appear above all admin tables

## Todo:

  * Make it an actual plugin
  * Add hooks/filters
