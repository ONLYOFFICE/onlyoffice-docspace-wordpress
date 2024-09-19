=== ONLYOFFICE DocSpace ===
Contributors: onlyoffice
Tags: onlyoffice, integration, docspace
Requires at least: 6.2
Tested up to: 6.3.1
Stable tag: 2.1.1
Requires PHP: 8.0
License: GPLv2
License URI: https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress/blob/master/LICENSE

ONLYOFFICE DocSpace plugin allows users to access ONLYOFFICE DocSpace from WordPress and add DocSpace rooms and files to the WordPress pages.

== Description ==

With ONLYOFFICE DocSpace plugin, you are able to use ONLYOFFICE DocSpace right within WordPress to create rooms, edit and collaborate on office docs, as well as you can add DocSpace rooms and files stored within these rooms to the WordPress pages when creating posts. 

**Exporting users to DocSpace**

To export users from your WordPress to ONLYOFFICE DocSpace, click the Export Now button on the plugin settings page. A page with the user list will open — it includes WordPress users with the upload_files permissions.

To add a user or several users to DocSpace, check them in the list, select Invite to DocSpace from the drop-down list and click the Apply button.

In the DocSpace User Status column of this list, you can track whether a WordPress user has been added to DocSpace or not:

- Green checkmark: a WordPress user with the specified email has been added to DocSpace. Synchronization was successful.
- Empty value: there is no WordPress user with the specified email in DocSpace. You can invite them.
- Hourglass: there is a user in DocSpace with the specified email, but there was a synchronization issue. When logging into the DocSpace plugin for the first time, the user will need to provide a DocSpace login and password to complete synchronization.

**Working with ONLYOFFICE DocSpace within WordPress**

After setting up the integration plugin, DocSpace will appear for users with the upload_files permission. Such users are able to access ONLYOFFICE DocSpace where it's possible to create Collaboration and Custom rooms, invite users, and collaborate on documents within the rooms.

**Adding a DocSpace room or file to the WordPress page**

When creating a post, you can add the ONLYOFFICE DocSpace element (block) – room or file.

To add a room, click the Select room button, select the desired room and press Select. In the block settings, you can specify the desired width and height to be displayed on the page.

To add a file, click the Select file button, select the desired file from the room and press Save.

Access rights to rooms and files on the published pages are determined depending on the publicity status of the WordPress page:

- Public: the DocSpace room/file is available for viewing to all WordPress users. These users access content under a public user account (WordPress Viewer).
- Private: the DocSpace room/file is available in accordance with the existing DocSpace access rights. Collaborative document editing is possible if users have the required rights.

== How the plugin is using the ONLYOFFICE DocSpace service ==

The plugin allows working with office files via [ONLYOFFICE DocSpace](https://www.onlyoffice.com/docspace.aspx) and makes the following requests to the service on the backend:

- getting a list of DocSpace users
- creating a user in DocSpace using WordPress user data
- getting a DocSpace user by email
- setting a password for a DocSpace user
- getting authorization cookies of a DocSpace user 
- getting a DocSpace file
- getting a DocSpace folder
- inviting a user to a DocSpace room

On the frontend, the following DocSpace elements are inserted:

- file selection control
- room selection control
- file display control
- room display control
- system frame for checking authorization 

*Useful resources:* 

- [ONLYOFFICE DocSpace Terms of use](https://onlyo.co/41Y69Rf)
- [Privacy Policy](https://www.onlyoffice.com/Privacy.aspx)

== Frequently Asked Questions ==

= How to configure the plugin? =

Go to WordPress administrative dashboard -> ONLYOFFICE DocSpace -> Settings. Specify the DocSpace Service Address, Admin Login and Password. When you click on the Save button, a user with the Room admin role will be created in ONLYOFFICE DocSpace, with the same data as the current WordPress user. A public user (WordPress Viewer) will be also added to DocSpace with the View Only access.

= What is ONLYOFFICE DocSpace? =

ONLYOFFICE DocSpace is a room-based collaborative environment. With ONLYOFFICE DocSpace, teams can create rooms with a clear structure entirely according to their needs and project goals and define from the start the required roles and rights that will apply to all the files stored within these rooms. DocSpace comes with the integrated online viewers and editors allowing you to work with files of multiple formats, including text docs, digital forms, sheets, presentations, PDFs.

== Screenshots ==

1. Adjust ONLYOFFICE DocSpace configuration settings within the WordPress administrative dashboard
2. Create collaboration and custom rooms in ONLYOFFICE DocSpace
3. Add ONLYOFFICE DocSpace rooms to the WordPress site
4. Add ONLYOFFICE DocSpace files to the WordPress site
5. Access ONLYOFFICE DocSpace within WordPress

== Changelog ==
= 2.1.0 =
* ability to add multiple rooms/files to a page
* block settings (view mode 'editor/embedded')
* hide sign out button on page docspace
* hide request name for anonymous
* structure of tables with files (Name,Size,Type)
* base theme in admin panel for docspace

= 2.0.0 =
* support for public rooms
* improved block settings (theme, align)
* improved view of the inserted blocks
* delete public user "Wordpress Viewer"

= 1.0.1 =
* minor code corrections, compliance with WordPress requirements
* fix invite users to DocSpace without first name or last name
* fix "DocSpace User Status", when the user has not confirmed the email

= 1.0.0 =
* connection settings page
* user synchronization
* opening DocSpace in WordPress
* inserting a file when creating a page
* inserting a room when creating a page
