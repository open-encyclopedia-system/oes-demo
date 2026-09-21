# Open Encyclopedia System — Demo Plugin

This repository contains the **OES Demo** plugin: an exemplary and fictional online encyclopedia built with the
[Open Encyclopedia System (OES)](https://github.com/open-encyclopedia-system/oes-core) framework, giving you a
first-hand look at OES's editorial and front-end functionality without setting up your own application first.

[![License: GPL v2](https://img.shields.io/badge/License-GPL_v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/open-encyclopedia-system/oes-demo/graphs/commit-activity)
[![AI-DECLARATION: assist](https://img.shields.io/badge/䷼%20AI--DECLARATION-assist-fef9c3?labelColor=fef9c3)](./AI-DECLARATION.md)

For general information about OES — what it is, its features, citation, contributing, credits, and licensing —
see the [OES Core README](https://github.com/open-encyclopedia-system/oes-core#readme).

A typical OES application consists of:
- the **OES Core** plugin
- an application-specific OES plugin, such as this **OES Demo** plugin
- an optional OES Theme, such as the **OES Block Theme**

## Dependencies

The OES Demo depends on:

| Component                    | Version   | Source                                                           |
|------------------------------|-----------|------------------------------------------------------------------|
| OES Core                     | `≥ 3.0.0` | [oes-core](https://github.com/open-encyclopedia-system/oes-core) |
| Advanced Custom Fields (ACF) | `≥ 6.3.4` | [advancedcustomfields.com](https://www.advancedcustomfields.com) |

## Installation

To get OES running locally or on a server, follow these steps:

1. **Install WordPress** on your system.
2. **Download and activate the required plugins**:
   - [OES Core Plugin](https://github.com/open-encyclopedia-system/oes-core)
   - [OES Demo Plugin](https://github.com/open-encyclopedia-system/oes-demo)
   - [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/)
3. (Optional) **Download and activate the** [OES Theme](https://github.com/open-encyclopedia-system/oes-block-theme)

If the installation was successful, you will now see the **OES** and **OES Tools** menu in the WordPress admin sidebar.
Navigate to **OES Tools → Data Model → Config** and click **Reload from Plugin Config** to import post types and
ACF fields (this requires admin privileges).

The OES Demo and its functionalities are now available in your WordPress installation.

> For a guided and more detailed setup, see the [OES Manual Installation & Einrichtung](https://manual.open-encyclopedia-system.org/) *(German)*.

### Permalinks

If you're using an OES Theme, refresh the permalink structure:
- Go to **Settings → Permalinks**
- Choose a permalink structure (we recommend **"Post name"**)
- Save the settings — even if no changes were made.

### Importing demo content

To import the demo data:
1. Install the [WordPress Importer plugin](https://de.wordpress.org/plugins/wordpress-importer/).
2. Use the `demo.xml` file located in the `data` folder of this repository.

You can begin configuring by exploring the OES settings (documentation coming soon) or by editing the `model.json`
file in this plugin.

## Documentation

The full user and technical manual is available at:
[OES Manual](https://manual.open-encyclopedia-system.org/) *(German)*

Additional documentation for this plugin, in this repository:

- [CHANGELOG.md](./CHANGELOG.md) — release history for the OES Demo plugin
- [ROADMAP.md](./ROADMAP.md) — planned features for the OES Demo plugin

## Support

This repository does not offer public support or issue tracking. For help using the OES plugins, contact:
**info@open-encyclopedia-system.org**

For general OES information — contributing and credits — see the
[OES Core README](https://github.com/open-encyclopedia-system/oes-core#readme).

## Licensing

This software is licensed under the **GNU General Public License (GPL v2 or later)**. See [LICENSE.txt](./LICENSE.txt)
for the full license terms, or [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).