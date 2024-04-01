# Pictap: Web Picture Gallery for PHP

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
* **Image Editing:** Perform lossless crop, rotation, and flipping directly from the browser.
* **Video Rotation:** Rotate mp4 videos losslessly.
* **Sorting Options:** Sort media by various criteria including name, date, size, duration, filetype and dimension.
* **Persistent Preferences:** Remember user preferences for sorting and scroll position on the device.
* **GPS Editing:** Edit GPS location information for media files.
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
* **Minimal Disk usage:** In our test of 1.4TB of 72000 media, the database size is 11MB, the thumbnail folder is 890MB, does not create any other sizes for now and uses original images for full size view, it maximizes space efficiency by employing the WebP format for thumbnails, offering both space-saving benefits and animated thumbnail capabilities.

## Minimum Requirements:

* **PHP 8+ with sqlite3 database support**
* **Linux Environment:** Tested on Debian (Raspberry PI bullseye).
* **Binaries:** Requires ffmpeg, exiftool, and jpegtran binaries. `sudo apt install ffmpeg exiftool libjpeg-progs`
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
│  ├─ /pictap.db (Main sqlite database, automatically generated on initial setup)
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

* Lossless Video Trim using ffmpeg
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
* Support Mysql/Postgres database engines


