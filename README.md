# ONLYOFFICE DocSpace plugin for WordPress

This plugin enables users to access ONLYOFFICE DocSpace, a room-based collaborative environment, from [WordPress](https://wordpress.org/), as well as add DocSpace rooms and files to the WordPress pages.  

## Plugin installation and configuration 

Navigate to the Plugins section in your WordPress administrative dashboard and click **Add New**. Then, click **Upload Plugin** and select the zipped plugin.

Once ready, go to the plugin settings and specify the following parameters:

- DocSpace Service Address
- Admin Login and Password

When you click on the Save button, a user with the **Room admin** role will be created in ONLYOFFICE DocSpace, with the same data as the current WordPress user. A public user (WordPress Viewer) will be also added to DocSpace with the View Only access. 

## Exporting users to DocSpace

You need to export users from your WordPress to ONLYOFFICE DocSpace. Click the **Export Now** button on the plugin settings page. A page with the user list will open (it includes WordPress users with the *upload_files* permissions).

To add a user or several users to DocSpace, check them in the list, select **Invite to DocSpace** from the drop-down list and click the Apply button.

In the **DocSpace User Status** column of this list, you can track whether a WordPress user has been added to DocSpace or not:

- Green checkmark: a WordPress user with the specified email has been added to DocSpace. Synchronization was successful.
- Empty value: there is no WordPress user with the specified email in DocSpace. You can invite them.
- Hourglass: there is a user in DocSpace with the specified email, but there was a synchronization issue. When logging into the DocSpace plugin for the first time, the user will need to provide a DocSpace login and password to complete synchronization.

## How to work with ONLYOFFICE DocSpace within WordPress

After setting up the plugin, DocSpace will appear for users with the *upload_files* permission. Such users are able to access ONLYOFFICE DocSpace where it's possible to create Collaboration and Custom rooms, invite users, and collaborate on documents within the rooms.

## How to add a DocSpace room or file to the WordPress page

When creating a post, you can add the ONLYOFFICE DocSpace element (block) – room or file. 

To add a room, click the **Select room** button, select the desired room and press Select. In the block settings, you can specify the desired width and height to be displayed on the page.

To add a file, click the **Select file** button, select the desired file from the room and press Save.

Access rights to rooms and files on the published DocSpace pages are determined depending on the publicity status of the WordPress page:

- Public: the DocSpace room/file is available for viewing to all WordPress users. These users access content under a public user account (WordPress Viewer).
- Private: the DocSpace room/file is available in accordance with the existing DocSpace access rights. Collaborative document editing is possible if users have the required rights.

Please note the following specifics for DocSpace rooms published on WordPress pages:

- DocSpace left menu is not avaiable;
- Navigation is possible within the added room only;
- If users have the Room admin or Power user role, they can create new files.

## How the plugin is using the ONLYOFFICE DocSpace service

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

## Project info

Official website: [www.onlyoffice.com](https://www.onlyoffice.com/)

Code repository: [github.com/ONLYOFFICE/onlyoffice-docspace-wordpress](https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress)

## User feedback and support

In case of technical problems, the best way to get help is to submit your issues [here](https://github.com/ONLYOFFICE/onlyoffice-docspace-wordpress/issues). 
Alternatively, you can contact ONLYOFFICE team on [forum.onlyoffice.com](https://forum.onlyoffice.com/).