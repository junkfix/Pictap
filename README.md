# Pictap-Fork: Web Picture Gallery Foreword

This is a fork of original Pictap from [junkfix](https://github.com/junkfix) which can be found [here](https://github.com/junkfix/Pictap). \
At the very first I have to thank him for his great work. Without it we wouldn't have so much fun with our existing photo album anymore and also my small modifications wouldn't have been possible. \
\
Pictap currently is the only photo web gallery that meets my expectations after leaving Synology DSM 6 PhotoStation (showing "albums" structured in existing folders, users with access rights, create public links to albums, and many more). And I tested arround 30... \
Playing arround with Pictap now for some time I noticed some wishes for improvements - which I tried to bring in myself. \
After some success I'ld like to share the new features and modifications I managed to implement.\
I hope to have id'd all the modifications correctly in the code so you can identify changes belonging together.

### Currently working on
- **Push message for errors**\
    Push http message to eg. pushover.net if eg. if user gets blocked.
- **Try to make folders public accessable**
    - Public albums are already possible.
    - Although they have some slight side effect:
        - We have to create an album for makeing them public.
        - Pictures currently don't show any information (location,...)
        - We cannot use the folder structure.
    - What if we just could share some subfolder with no rights but display images.
    - Currently trying to understand and solve some security issues.
### Version 2.0.9.1
- **Browser tab title**\
  Set the browser tab title same as the configured page title.
- **Create random public url name for public albums**\
  Try to hide public albums a bit by using random names instead of their original (like other galleries do).
- **Redirect to the desired location after login**\
  Try share a link to a folder.\
  The user has to login.\
  After login the user has been forwarded to his root folder instead of the given link.
  Now the user will be directed to the shared link.
- **Log if user IP gets blocked**\
  Log if user in fact gets blocked after faulty logins (page user accounts, failed_logins.log)
- **All image info text same brightness**\
  City link was color 'a' which was slightly darker than eg. image name.
- **Adapted gallery folder color to gray design**\
  Changed gallery folder from yellow to gray to adopt to gray design.
- **Implemented some changes/fixes from Pictap 2.0.9 back to Pictap-Fork**\
  - Support special characters in file names.
  - Title configurable.
  - Favicon configurable.
  - User without user_folder configurable.
  - Show logged in user name.
  - Loop gallery on/off configurable.
  - Boolean setting with default == 1 couldn't be saved to 0.
  - Delete Album: right click, remove album -> Error: You have an error in your SQL syntax (with mysql).
- **Fixed**
  - **Gallery didn't show any folders if name_regex didn't exist**\
    eg. after update from 2.0.8 to 2.0.8.1

### Version 2.0.8.1 Modifications to Original Pictap 2.0.8
- **Support special characters 'äöü' in filenames**\
  On Linux (Diskstation DSM 7) files and folders containing special characters wouldn't be shown.\
  On Windows 10 (with xampp) this hasn't been an issue. The change didn't effect Windows compatibility.
- **Title configurable**\
  I just found it nice to have a configurable web page title.
- **Favicon configurable**\
  Same with the favicon.\
  Here I am not very sure about the icon I had to switch off. Maybe I'll find a solution.
- **User without user_folder configurable**\
  Our existing folder gallery just is a family gallery with users having some rights but without user folders.\
  Now you can switch off creation of user folders and each user will login to main folder.
- **Show logged in user name**\
  When testing back and forth it was nice to see the test users on the different windows I logged in.\
  The name shows in the left menu.
- **Loop gallery on/off configurable**\
  Showing photos sometimes is confusing when the gallery at the end loops from last to first image - "did we have this one already ?"
- **Name all roles explicitly** instead of binary 0xfff + delete some\
  Just a small change about defining user default roles.
- **Images sort by DTOriginal** instead of sort by DTModified\
  I found that images date sort happened by modified date. When I create albums I often modify images. So the timeline wouldn't match.\
  Currently this is a fixed (non configurable) change.
- **Tree default sort up by name**\
  Left menu folders tree sort now looks more similar to a file explorer.
- **Folders in gallery always sort by name**\
  Like above... For folders in gallery it seemed more pretty to always sort them by name. Because they don't have a original/taken timestamp.
- **Show DTOriginal on gallery**\
  For us this seemed more reasonable than the modified tiemstamp.
- **Split folder names**\
  Found a funny behaviour: Folder thumbnail sizes sometimes were of different height in gallery tiles view.\
  After some search I did notice our very long folder names.\
  Now you can configure a regex to split, shorten, ... the names eg. split at '_', '-'.
- **Sort default configurable**\
  This feature makes the files default sort configurable by admin (currently not per user).\
  As mentioned above folders will be sorted by name always.
- **Boolean settings with default == 1 couldn't be saved to 0**\
  This is because checkboxes only submit their value if checked. If we uncheck and submit no information from this checkbox will be submitted.\
  One solution: In itick() we have to add a hidden input with value 0 which will be submitted if the checkbox itself is unchecked and doesn't submit.\
	See https://mimo.org/glossary/html/checkbox "Form Submission and Checkbox Value Behavior"
### Non software modifications
- **Updated gps.sql with more locations**\
  Found that [umer2001/py_countries_states_cities_database](https://github.com/umer2001/py_countries_states_cities_database/raw/refs/heads/master/) in our surroundings and also places we visited contains more small towns.\
  The new gps.sql I converted from its json files.\
  Note that it obviously must be bigger than the original which appears to be no issue for my DS224+.\
  Many thanks to [umer2001](https://github.com/umer2001).\
<br><br>


# Pictap: Web Picture Gallery for PHP  (the original by junkfix)

Pictap is a versatile web-based picture gallery, offering a plenty of features for managing and sharing your media collection.
Unlike other online solutions, Pictap is tailored specifically for home users seeking to securely store their mobile images on a local network drive, such as a Raspberry Pi. Think of it as a replacement for Google Photos, allowing you to curate your collection, share selected holiday photos with friends and family, and seamlessly backup and access your images from anywhere in the world.

[Goto original Pictap project](https://github.com/junkfix/Pictap)
