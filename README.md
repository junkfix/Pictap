# Pictap: Web Picture Gallery Foreword

Pictap currently is the only photo gallery that meets my expectations after leaving Synology DSM 6 PhotoStation (showing "albums" structured in existing folders, users with access rights, create public links to albums, and many more).
After playing arround for some time I noticed some wishes for improvements - which I tried to bring in.\
Now where there is some success I would like to share the new features and modifications.\
\
But at the very first I have to thank [junkfix](https://github.com/junkfix/Pictap) for his great work. Without it we wouldn't have so much fun with our photo album anymore and also my small modifications wouldn't have been possible.\
\
*Currently this is the original Pictap.\
Please be patient when this forking process is taking some time since for me Pictap is the first project to fork and publish.*

Modifications to Pictap 2.0.8 I'ld like to share:
- FIXED 1		support special characters 'äöü' in filenames\
  On Linux (Diskstation DSM 7) files and folders containing special characters wouldn't be shown.\
  On Windows 10 (with xampp) this hasn't been an issue. The change didn't effect Windows compatibility.
- FEATURE 2	title configurable\
  I just found it nice to have a configurable web page title.
- FEATURE 3	favicon configurable\
  Same with the favicon.\
  Here I am not very sure about the icon I had to switch off. Maybe I'll find a solution.
- FEATURE 4	user configurable without user_folder\
  Our existing folder gallery just is a family gallery. Now you can switch of creation of user folders and each user will login to main folder. He obviously should have the right to see all.
- FEATURE 5	show logged in user name\
  When testing back and forth I found it nice to see the test users on the different windows I logged in.\
  The name shows in the left menu.
- FEATURE 6	loop gallery configurable on/off\
  Showing photos sometimes is confusing when it rotates from last to first image - "did we have this one already ?"
- MODIFIED 7	name all roles explicitly instead of binary 0xfff + delete some\
  Just a small change about defining user default roles.
- MODIFIED 8	images sort by DTOriginal: instead of sort by DTModified\
  I found that images date sort happened by modified date. When I create albums I often modify images. So the timeline wouldn't match.\
  Currently this is a fixed (non configurable) change.
- MODIFIED 9	tree default sort\
  As above we usually sort our albums just by time. So, would be nice to set this as default instead of name sort.
- MODIFIED 10	folders always sort by name\
  Instead of above... For folders it seems better to always sort them by name. Because they don't have a original/taken timestamp.
- MODIFIED 11	show DTOriginal on gallery\
  For us this seemed more reasonable than the modified tiemstamp.
- FEATURE 12	split folder names\
  Found a funny behaviour: folder thumbnail sizes sometimes were different in height in gallery tiles view.\
  After some search I did notice our very long folder names.\
  Now you can configure a regex to split, shorten, ... the names.
- FEATURE 13	sort default configurable\
  This change made the default sort configurable.
- OPEN 14 there seems to be a tiny issue with boolean settings which default to 1: a change to 0 seems to be not saved.\
  This I noticed when adding a new setting.\
  After turning the default of this new setting to 0 it could be saved.


# Pictap: Web Picture Gallery for PHP  (the original by junkfix)

Pictap is a versatile web-based picture gallery, offering a plenty of features for managing and sharing your media collection.
Unlike other online solutions, Pictap is tailored specifically for home users seeking to securely store their mobile images on a local network drive, such as a Raspberry Pi. Think of it as a replacement for Google Photos, allowing you to curate your collection, share selected holiday photos with friends and family, and seamlessly backup and access your images from anywhere in the world.

## Key Features:

* **Multi-User Support with Access Control:** Securely manage multiple users with customisable access permissions.
* **User-Friendly Setup:** Easily configure settings/users directly from your browser, no need for manual configuration file edits.
* **Preservation of Folder Structure:** Maintain your existing folder hierarchy for seamless integration.
* **Built-in GPS Database:** Includes major locations for geotagging images.
* **Progressive Web App (PWA):** Enjoy the convenience of installing Pictap as a Progressive Web App, compatible with Android devices, not tested on ios.
* **Album Creation:** Organise your media into albums for efficient categorisation.
* **Shared Albums:** Share albums publicly via link sharing.
* **Drag-and-Drop Uploads:** Effortlessly upload files and folders with detailed progress tracking.
* **Direct Upload Support:** Enable direct uploads from any other apps using share on Android through the installed web app, untested on ios.
* **Shared Folder Access:** Facilitate shared folder access among family group users.
* **Security Features:** Implement measures such as limiting login attempts and enabling global logout, passwords are stored as hash in config file.
* **Media Support:** Pictap supports both videos and images using PhotoSwipe.
* **Search:** Search media by name, city, country, state, exif device name.
* **Image Editing:** Perform lossless crop, rotation, and flipping directly from the browser (also supports batch editing).
* **Video Rotation:** Rotate mp4 videos losslessly (also supports batch editing).
* **Video Trim:** Trim mp4 videos losslessly, can also mute audio.
* **Sorting Options:** Sort media by various criteria including name, date, size, duration, filetype and dimension.
* **Persistent Preferences:** Remember user preferences for sorting and scroll position on the device.
* **GPS Editing:** Edit GPS location information for media files (also supports batch editing).
* **Map Integration:** View image locations on Google Maps.
* **Animated Video Thumbnails:** Enjoy animated thumbnails for video previews.
* **Custom Folder Images:** Set custom folder images directly from the browser.
* **Image Classification:** Optionally utilise TensorFlow for image classification to extract keywords.
* **Timeline View:** Browse media by month in a timeline format.
* **Day Exploration:** Easily explore media by specific days.
* **File Management:** Perform various file operations including moving, renaming, deleting, and creating directories.
* **Auto Rename Option:** Auto Rename IMG_/VID_date_time.\* to date_time.\*
* **Multi-Select Support:** Select and perform operations on multiple items simultaneously.
* **Download and Sharing:** Download and share media files directly.
* **Slideshow Autoplay:** Automatic slideshow playback.
* **Caching Support:** Utilise IndexedDB caching for faster browser access.
* **Folder Tree Menu:** Navigate efficiently with a folder tree menu in the navbar.
* **Customisable Menu Sorting:** Sort the folder menu tree by name, size, or date.
* **View Options:** Choose between list and grid views.
* **Context Menus:** Access context menus for quick actions.
* **Recycle Bin:** Optionally enable a recycle folder to store deleted media.
* **Lightweight and Self-Contained:** Pictap has minimal dependencies, single php/js/css, requiring only around 300KB of space, along with an additional 2MB for the GPS database sourced from [SimpleMaps](https://simplemaps.com/).
* **Minimal Disk usage:** In our test of 1.4TB of 72000 media, the sqlite database size is 11MB, the thumbnail folder is 890MB, does not create any other sizes for now and uses original images for full size view, it maximizes space efficiency by employing the WebP format for thumbnails, offering both space-saving benefits and animated thumbnail capabilities.

## Minimum Requirements:

* **PHP 8+** supported databases are PostgreSQL/MySQL/Sqlite3
* **Linux/Windows Environment:** Tested on Debian (Raspberry PI bullseye) and Windows 11.
* **Binaries:** 
  * **Linux:** Requires ffmpeg, exiftool, and jpegtran binaries. `sudo apt install ffmpeg exiftool libjpeg-progs`.
  * **Windows:** Requires ffmpeg.exe, exiftool.exe, and jpegtran.exe binaries. [ffmpeg-master-latest-win64-gpl.zip
](https://github.com/BtbN/FFmpeg-Builds/releases), [exiftool](https://exiftool.org/) rename exiftool(-k).exe to exiftool.exe, [jpegtran](https://jpegclub.org/jpegtran/)
* **Web Server:** nginx (or Apache) with optional SSL support for the PWA web app.
* **Optional TensorFlow Setup:** Required only if utilising image classification keywords.

## Installation / Folder Structure
```
WEBROOT
├─ /pictures (Your original images here, each users will have their own folder in here, a family folder for shared media between users)
├─ /thumbs (Thumbnails are auto-generated on first load, can also be pre-generated from CLI)
├─ /shared (Contains symlinks for shared album public access to save space usage)
├─ /data (Sensitive data can stored outside webroot or protect using server config)
│  ├─ /auth (Stores login sessions for user authentication)
│  ├─ /badip (Prevents brute force login attacks by storing blocked IP addresses)
│  ├─ /pictap.db (For sqlite database only, automatically generated on initial setup)
│  ├─ /keywords.* (Script for TensorFlow keyword extraction via CLI)
├─ pictap.php (Main PHP file; can be renamed for customisation)
├─ share.php (For public album sharing functionality)
├─ pictap_config.php (Automatically generated configuration file upon initial setup)
├─ pictap.js
└─ pictap.css
```

### Limitations
* If the folder has 2 images with same name eg `photo.jpg`, and `photo.png` it will auto rename one eg. `photo_1.png` so thumbnail can be served.
* Only empty directories can be deleted from the browser.
* Recycle folder cannot be viewed from the browser.
* Access to the '/pictures and /thumbs' directory should be restricted to logged-in users. Configuration files for nginx/Apache can be found in the '/data' directory.

### TODO

* ~Lossless Video Trim using ffmpeg~
* Additional EXIF/IPTC Keywords/Tags from images
* Online update
* Add more view modes / improve and arrange UI / dark / light modes
* Add storage quota for each user to limit the storage space
* Add TensorFlow facenet for face recognisation and group photos by face
* Duplicates find and delete
* Add option to generate multiple image sizes for faster access when viewed on a small screen.
* Add Audio support
* Make non streamable videos streamable for eg. mpg/avi
* Convert other image formats (eg. HEIC) to jpg
* Create cron tasks to monitor changes in the image folder
* Auto upload for mobile (when PWA can remember directory permission across the sessions)
* Add custom new location places in the gps databases
* Fix EXIF Date taken from the browser
* ~Support Mysql/Postgres database engines~

### Screenshots

![](/screenshots/screenshot1.jpg?raw=true)

![](/screenshots/screenshot2.jpg?raw=true)

![](/screenshots/screenshot3.jpg?raw=true)

![](/screenshots/screenshot4.jpg?raw=true)
