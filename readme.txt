=== ONLYOFFICE DocSpace ===
Contributors: onlyoffice
Tags: onlyoffice, integration, docspace
Requires at least: 6.2
Tested up to: 6.3.1
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv2
License URI: https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress/blob/master/LICENSE

ONLYOFFICE plugin allows users to edit and view office documents from WordPress using ONLYOFFICE Docs.

== Description ==

ONLYOFFICE integration plugin allows WordPress administrators to open documents, spreadsheets, and presentations for collaborative editing using ONLYOFFICE Docs (online document editors). In published posts, the editors are visible to all WordPress site visitors (both authorized and unauthorized) in the Embedded mode only.

**Editing files uploaded to Wordpress**

All uploaded files from the Media section will appear on the ONLYOFFICE -> Files page. The editor opens in the same tab by clicking on the file name. Users with administrator rights are able to co-edit documents. All the changes are saved in the same file.

**Creating a post**

When creating a post, you can add the ONLYOFFICE element (block) and then upload a new file or select one from the Media Library. The added file will be displayed as the ONLYOFFICE logo with the file name in the currently edited post. After the post is published (when you press the Publish or Update button), your WordPress site visitors will have access to this file for viewing in the Embedded mode.

== Frequently Asked Questions ==

= What should I know before using the plugin? =

You need to have [ONLYOFFICE Document Server](https://github.com/ONLYOFFICE/DocumentServer) installed. You can install free Community version or scalable Enterprise Edition.

= How to configure the plugin? =

Go to WordPress administrative dashboard -> ONLYOFFICE -> Settings. Specify the URL of the installed ONLYOFFICE Document Server and the Secret key.

Please note: Starting from version 7.2 of ONLYOFFICE Docs, JWT is enabled by default and the secret key is generated automatically to restrict the access to ONLYOFFICE Docs and for security reasons and data integrity. Specify your own secret key in the WordPress administrative configuration. In the ONLYOFFICE Docs [config file](https://api.onlyoffice.com/editors/signature/), specify the same secret key and enable the validation.

= What collaborative features do the editors provide? =

You can co-author documents using real-time or paragraph-locking co-eding modes, Track Changes, comments, and built-in chat.

== Screenshots ==

1. ONLYOFFICE plugin configuration settings within the WordPress administrative dashboard.
2. ONLYOFFICE -> Files page within the WordPress administrative dashboard.
3. ONLYOFFICE document editor opened from the WordPress admin dashboard.
4. Adding ONLYOFFICE block when creating a post.
5. Uploading a new file or selecting one from the Media Library to the ONLYOFFICE block.
6. Added file displayed as the ONLYOFFICE logo with the file name in the currently edited post.
7. ONLYOFFICE file available for viewing in the Embedded mode to the WordPress site visitors.

== Changelog ==

= 1.0 =
* connection settings page
* user synchronization
* opening docspace in wordpress
* inserting a file when creating a page
* inserting a room when creatinf a page

== Upgrade Notice ==

= 1.0 =
This is the first version of the plugin.