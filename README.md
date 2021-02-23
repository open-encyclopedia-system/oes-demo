# Open Encyclopedia System Demo Plugin (OES Demo Plugin)

Welcome to the Open Encyclopedia System Demo (OES Demo) repository on [GitHub](https://github.com/open-encyclopedia-system/oes-demo). OES is a modular and configurable software for 
creating, publishing and maintaining online encyclopedias in the humanities and social sciences that are accessible 
worldwide via Open Access. For more information please visit the [main repository](https://github.com/open-encyclopedia-system/oes-core) or our [website](http://www.open-encyclopedia-system.org/).

A typical OES application consists of an OES plugin and an additional OES project plugin which implements the OES 
features for the application. The OES Demo plugin is an exemplary OES project plugin allowing you to experience an exemplary application. This application includes a basic online encyclopedia and should be used with the OES Demo WordPress theme.


## Documentation

We are working on a detailed user manual. <br>
The OES Demo plugin includes a function reference. You can access the function reference after downloading the sources 
by navigating to the <strong>function-reference</strong> folder inside the OES Demo plugin directory and opening 
index.php with a browser. <br>
If you want to access the function reference as part of your website we recommend moving the 
<strong>function-reference</strong> folder to the WordPress directory (on the same level as <strong>wp-content</strong> 
etc.). The function reference is then available at [your website url]/function-reference.


## Getting Started

### Dependencies

OES depends on the OES Core plugin:
* OES Core, version 0.5., URL: https://github.com/open-encyclopedia-system/oes-core

The exemplary frontend depends on the OES Demo Theme:
* OES Demo Theme, version 0.1., URL: https://github.com/open-encyclopedia-system/oes-demo-theme

### Installation

1. <strong>Install the OES Core plugin and its dependencies:</strong>
Follow the installation instructions for the OES Core plugin as described in the [README.md](
https://github.com/open-encyclopedia-system/oes-core/blob/master/README.md).
(This includes downloading and activating the ACF plugin and optional the Classic Editor plugin on which the OES Core  plugin depends). 
Activate first the ACF (and the Classic Editor) plugin and then the OES Core plugin.

2. <strong>Install the OES Demo plugin:</strong>
Download the OES Demo plugin sources into the WordPress plugin directory. Activate the plugin.

3. <strong>Install the OES Demo Theme plugin:</strong>
Download the OES Demo Theme sources into the WordPress theme directory. Activate the theme. The theme needs to be 
activated <strong>after</strong> you have activated the OES Demo plugin!

If the installation was successful you will now see the menu "OES Settings" inside the WordPress admin interface in the 
navigation menu on the left (as well as custom post types such as "Articles", "Articles Master", "Glossary", etc.). 
The OES Demo and its functionalities are now available inside your WordPress installation.

If you want to use the included OES Demo data, proceed with the following steps:

4. <strong>Import data (Optional):</strong>
Go to the OES Demo plugin directory and find the file sqldump.sql inside the temp/sqldump directory. Import the file 
into your sql database (see https://dev.mysql.com/doc/workbench/en/wb-admin-export-import-management.html for more 
information about sql import tools).

5. <strong>Import default settings (Optional):</strong>
The OES Demo plugin offers various configuration options via the WordPress admin interface. For more information about 
OES configurations go to the menu "OES Settings"/"Editorial Layer" inside the WordPress admin interface. 
If you want to use an initial configuration go to the OES Demo plugin directory and find the file option-defaults.json 
inside the temp/to_uploads directory . 
Move the file to the WordPress uploads directory (wp-content/uploads). Make sure that the directory is editable.
Inside the WordPress admin interface go to the menu "OES Settings"/"Editorial Layer" and choose the option "Configure 
via Editorial Layer" and click "Save Changes" (as a result the post types "Article", "Glossary", etc. are no longer visible in the 
navigation menu on the left). Click the button "Import Default Options" (the post types are visible 
again).

6. <strong>Customize settings (Optional):</strong>
You can customize the OES settings by following the instruction in the menu "OES Settings"/"Editorial Layer" and
"OES Settings"/"OES Theme".

## Roadmap

The OES Demo is an exemplary project plugin to implement OES features. We are working on further feature implementation 
and the development of more features. Here is a peak of our roadmap what the OES Demo 2.0 will include:
- A detailed user manual,
- API to Zotero to synchronise data and a generalised API to import and export external data,
- A user right role model to grant specific permissions inside the editorial layer,
- Enhanced security features,
- Improved and enhanced import tool,
- Tools to export entities like articles to xml.

We hope to release OES Demo 2.0 by end of 2021.


## Support

This repository is not suitable for support.

Support is currently provided via our email help desk info@open-encyclopedia-system.org. We answer questions related to 
the OES plugin, the OES Demo plugin, the OES Demo Theme and its usage. For further information about online 
encyclopaedias and possible customization please visit our [website](http://www.open-encyclopedia-system.org/). 


## Contributing

If you want to contribute to OES please contact the help desk info@open-encyclopedia-system.org.


## Licencing

Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. 

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.