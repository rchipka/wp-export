# wp-export

Export WordPress users or posts tables (works with custom post types) to CSV

## What makes it special

Most WordPress CSV table export plugins attempt to manually rebuild the admin tables in CSV format, which causes issues with filtering, custom columns, and custom fields.

Instead, this plugin captures the HTML output of the actual admin table, sets `posts_per_page` to unlimited, sanitizes the columns, and converts the result to a CSV.

Whatever you see in the admin table is what you get in a CSV.

HTML will be gracefully downgraded to plain text.

## Supports

 * [x] custom post types
 * [x] custom columns
 * [x] filtered column views
 * [x] filtered post status view
 * [x] users table
 * [x] unicode

## Features

 * Exports WordPress table data as you see it in the admin view
 * Works with Admin Columns Pro table columns
 * Better than built-in Admin Columns Pro export functionality
 
## Installation

`include` or `require` export.php and an *Export* button will appear above all admin tables

## Todo:

  * Make it an actual plugin
  * Add hooks/filters
