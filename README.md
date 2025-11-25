# Open Encyclopedia System Plugin
Welcome to the Open Encyclopedia System (OES) Demo repository on GitHub.  
OES is a modular and configurable software platform for creating, publishing, and maintaining online encyclopedias in the humanities and social sciences. It is designed to be accessible worldwide through Open Access.

For more information, please visit the [main repository](https://github.com/open-encyclopedia-system) or our [website](https://open-encyclopedia-system.org).

A typical OES application consists of:
- the **OES Core plugin**
- a **project-specific OES plugin**, such as this OES Demo plugin

The **OES Demo** plugin is an exemplary and fictional online encyclopaedia created with the OES framework to provide users a first-hand experience of OES functionalities and features.

## Dependencies
The OES Demo depends on:

- **OES Core**, version `2.3.3`  
  Repository: [https://github.com/open-encyclopedia-system/oes-core](https://github.com/open-encyclopedia-system/oes-core)

- **Advanced Custom Fields (ACF)**, version `6.3.4`  
  Website: [https://www.advancedcustomfields.com](https://www.advancedcustomfields.com)

## Installation
1. Download the OES plugin from GitHub and add it to your WordPress plugin directory.
2. Download and activate the dependencies:
  - **OES Core** (see above)
  - **Advanced Custom Fields (ACF)**
3. Activate the OES plugin.
4. Create your own OES project plugin, or download and activate the OES Demo plugin.
5. *(Optional)* Download and activate the OES theme.

If the installation was successful, you will now see the "OES Settings" menu in the WordPress admin interface on the left.  
Navigate to **"OES Tools" > "Data Model" > "Config"** and click **"Reload from Plugin Config"** to import post types and ACF fields (this requires admin privileges).

The OES Demo and its functionalities are now available in your WordPress installation.

If you're using an OES theme, you may need to refresh the permalink structure:
- Go to **Settings > Permalinks**
- Choose a permalink structure (we recommend **"Post name"**)
- Save the settings — even if no changes were made.

You can begin configuring by exploring the OES settings (documentation coming soon) or by editing the `model.json` file in your project plugin.

To import demo data, install the [WordPress Importer plugin](https://de.wordpress.org/plugins/wordpress-importer/) and use the `demo.xml` file located in the `data` folder of this repository.

## Support
This repository does **not** offer public support or issue tracking.  
If you need help using the OES plugins, please contact our help desk:  
**info@open-encyclopedia-system.org**

For information about available modules, customization options, or help launching your own encyclopedia, visit:  
[https://open-encyclopedia-system.org](https://open-encyclopedia-system.org)

## Documentation
The full user and technical manual is available at:  
[https://manual.open-encyclopedia-system.org/](https://manual.open-encyclopedia-system.org/)

## Contributing
If you are interested in contributing to OES development, please get in touch:  
**info@open-encyclopedia-system.org**

## Credits
Developed by **Digitale Infrastrukturen**, Freie Universität Berlin (FUB IT),  
with support from the **German Research Foundation (DFG)**.

## Licencing
Copyright (C) 2025
Freie Universität Berlin, FUB IT, Digitale Infrastrukturen
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
