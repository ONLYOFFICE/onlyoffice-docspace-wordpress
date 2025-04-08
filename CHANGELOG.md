# Change Log

## 3.0.1
## Changed
- fixed compatibility of the Gutenberg editor element, ONLYOFFICE DocSpace between versions of the plugin 2.x and 3.x

## 3.0.0
## Added
- Disconnect button in connection settings
- setting Log in as a DocSpace Admin
- creating shared group WordPress Users after connecting DocSpace
- creating shared group WordPress Users after login System User
- inviting to shared group users which authorize in plugin
- inviting created through the plugin users to shared group WordPress Users
- drop saved DocSpace Account after sign out in DocSpace(Sign out button)
- reset password on login page
- action Unlink DocSpace Account on Users page

## Changed
- export functionality to DocSpace has been moved to the main Users page
- export users from wordpress to docspace with role User (rename User to Guest, PowerUser to User)
- error stubs (unavailable, unauthorized)
- settings page design

## 2.1.2
## Changed
- fixed xss vulnerabilities in onlyoffice-docspace page component

## 2.1.1
## Changed 
- DocSpace JS SDK version 1.0.1
- use username in user export data if first_name and last_name is empty 

## 2.1.0
## Added 
- ability to add multiple rooms/files to a page
- block settings (view mode 'editor/embedded')

## Changed
- hide sign out button on page docspace
- hide request name for anonymous
- structure of tables with files (Name,Size,Type)
- base theme in admin panel for docspace

## 2.0.0
## Added
- support for public rooms
- improved block settings (theme, align)
- improved view of the inserted blocks

## Changed
- delete public user "Wordpress Viewer"

## 1.0.1
## Changed
- minor code corrections, compliance with WordPress requirements
- fix invite users to DocSpace without first name or last name
- fix "DocSpace User Status", when the user has not confirmed the email

## 1.0.0
## Added
- connection settings page
- user synchronization
- opening docspace in wordpress
- inserting a file when creating a page
- inserting a room when creatinf a page
