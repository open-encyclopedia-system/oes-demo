# Changelog

## 3.0.0 - 2026-09-22

- **Raises minimum required OES Core version to `3.0`.** Demo's plugin initialization has been rewritten to use
  Core 3.0's new initialization mechanism and will not activate on Core versions below `3.0`.
- Significant logic previously implemented within the Demo plugin (especially post processing) has been
 **moved into OES Core** and is **integrated into OES Block Theme**. Demo now only contains the initialization and 
 application-specific configuration required to load this logic from Core.

### Compatibility notes

- This is a **one-directional break**: OES Core `3.0` remains backward-compatible with Demo `2.3.x` — you are
  not required to upgrade Demo when upgrading Core. However, Demo `3.0.0` requires Core `3.0` or later and will
  not run on earlier Core versions. It is recommended to update the OES Block Theme as well.
- If you are not ready to upgrade Core, you can continue running Demo `2.3.x` without change.

### Changed

- Plugin initialization now defers to OES Core's initialization API rather than bootstrapping independently.
- Switch languages: primary language is now German, secondary English

### Removed

- Removed post single and archive processing code now provided by blocks and shortcodes in OES Core `3.0`.

### Unchanged

- The data model and demo content (`model.json`, `data/demo.xml`) remain functionally the same as in `2.3.x`;
  no data migration is required when upgrading.

---

## 2.3.1

- remove field_demo_event__media
- remove some post processing -> moved to theme blocks
  - e.g. Contributor Index Entry, Contributor Further Publications
  - remove Place oes_map_html

