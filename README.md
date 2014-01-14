oempro-export-to-pdf
====================

oempro Plugin for export a campaign to pdf


# How install it


put exportpdf in plugins folder

if your operating system isn't 64 bits architecture you need to make some changes:
 Edit file exportpdf/includes/wkhtmltopdf.php
 Find line with > private static $cpu='amd64'; // force 64 bits by bruno sousa 
 and change amd64 to i386

go to administration in oempro find "Plug-ins" in menu and activate the plugin "Export to Pdf"

and now your are ready to use it


# Credits to
https://code.google.com/p/wkhtmltopdf/




