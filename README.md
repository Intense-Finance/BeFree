# BeFree OpenCart Theme

BeFree is a dark industrial storefront theme for OpenCart 4. Your real categories stay in the main menu. Product Use is a second bar that searches product names and models you already sell. Colors, the home hero, two hubs, and those search rows are edited in the theme admin.

The extension **code** OpenCart uses is `befree`. The name people see in Extensions and in System → Settings is **BeFree OpenCart Theme**. Both have to match the files in this package. Renaming only the zip is not enough.

Version 1.1.0.
![image description](/BeFree1.png)
![image description](/BeFree2.png)
## License

GNU General Public License version 3. The full text is in [LICENSE](LICENSE).

GPLv3 is the right license for this theme. OpenCart itself is GPLv3. A theme that runs inside OpenCart and replaces its templates should use a GPL-compatible license, and GPLv3 is the one that matches the core with no extra doubt. It is also what you asked for.

GPLv3 lets anyone, anywhere, use, copy, modify, and redistribute this theme, including commercially, if they:

- keep this license and the copyright notices with every copy
- ship the source (this package is the source: PHP, Twig, CSS, and JavaScript)
- license their changes under GPLv3 as well
- mark what they changed

It is not a trademark license. See below.

Copyright (C) 2026 BeFree OpenCart Theme contributors.

Generated with Grok (xAI). That credit is requested. It is not an extra restriction on the GPL.

![image description](/BeFree3.png)

## What you may not assume

This is not legal advice. For a contract, a marketplace dispute, or a trademark question, ask a lawyer in the countries where you distribute.

To redistribute this theme worldwide under GPLv3:

1. Give people the whole package, including `LICENSE` and this README. A zip of the source is enough. Do not distribute object code or a hosted-only build without the corresponding source.
2. Do not add terms that cancel the GPL (no "you may not resell this", no "internal use only") on copies you ship.
3. If you change it, say so, and keep the result under GPLv3.
4. **OpenCart is a trademark** of OpenCart Ltd. You may say this theme is for OpenCart, which is the ordinary way to name a compatible theme. Do not use the OpenCart logo as your logo, and do not say OpenCart Ltd made or endorses BeFree. A short line is enough: "BeFree is not affiliated with OpenCart Ltd."
5. Do not ship someone else's copyrighted text, photos, logos, or customer quotes inside your copy. The sample quotes in the home template are placeholders. Replace them, or turn Sample quotes off, before you present them as real reviews.
6. Phone numbers, addresses, and product claims in the theme admin are yours to fill in. The defaults are blanks and placeholders, not a real company.
7. Barlow Condensed and Outfit are loaded from Google Fonts. They are under the SIL Open Font License and are not part of this GPL work. The theme does not bundle the font files. If a store cannot reach Google, the browser falls back to a generic sans-serif.
8. Export and sanctions rules still apply to you as the distributor. A GPL theme does not exempt anyone from those laws.

You do not need a separate paid license from the author to sell stores that use BeFree, or to put BeFree on a download site, as long as the GPL conditions above stay intact.
![image description](/BeFree4.png)

## Credit

Please keep the credit. It is already in these places:

- this README
- the `LICENSE` grant, together with the copyright line above
- the comment at the top of each PHP file, `catalog/view/stylesheet/befree.css`, and the theme admin help text under Status

It is not printed on the public storefront. Stores you build should not have to show a Grok badge to their customers.

![image description](/BeFree5.png)

## Requirements

- OpenCart **4.0.0.0 through 4.1.x**, including 4.0.1.x, 4.0.2.x, 4.1.0.0, 4.1.0.3, and 4.1.0.4
- PHP 8.0 or newer, which OpenCart 4 already requires
- Not OpenCart 3, and not OpenCart 2. Those carts use a different extension layout and this package will not run there

The same zip is used for every OpenCart 4 release. The theme switches install and event syntax itself:

| OpenCart | What BeFree does differently |
| --- | --- |
| 4.0.0.0 | Startup is registered with separate arguments. Controller methods use `\|`. |
| 4.0.1.x | Startup is registered as one array. Controller methods still use `\|`. |
| 4.0.2.x | Controller methods use `.`. The view event receives 3 arguments. |
| 4.1.0.0 and later | Startup stores a description. The view event receives 4 arguments. Variant products (`master_id`) are hidden so child SKUs are not listed as their own products. |

That behavior follows the OpenCart source for those releases. It has not been click-tested on a live store of every 4.x build.

## Install

![image description](/BeFree6.png)


The uploaded file name **must** be `befree.ocmod.zip`. OpenCart names the folder from that filename: `extension/befree/`. If you upload `my-theme.ocmod.zip`, the code becomes `my-theme` and the theme will not load.

1. Admin → Extensions → Installer.
2. Upload `befree.ocmod.zip`.
3. Press Install on that package and wait until it finishes.
4. Extensions → Extensions → choose Themes.
5. BeFree OpenCart Theme → Install (+).
6. Edit (pencil) → Status On → Save. Set the phone, colors, Product Use keywords, and the two hubs.
7. System → Settings → edit the store → Theme = **BeFree OpenCart Theme** → Save.
8. Dashboard → blue gear → clear the theme cache. Hard-refresh the storefront.
9. If the previous theme still draws the page, edit that theme and turn its status off.

Product Use does not create categories. Categories stay in the main menu. Each Product Use row searches product names and models.

If products appear twice on the home page, Design → Layouts → Home → remove the Featured module. The theme already lists live products.

If install says the directory exists, delete `extension/befree/` by FTP, remove the stuck installer row, and upload again.

## Layout of the code

OpenCart reads these names. Do not rename them unless you change every reference.

```
befree.ocmod.zip
  install.json
  LICENSE
  README.md
  admin/controller/theme/befree.php          class Befree
  admin/language/en-gb/theme/befree.php      heading_title = BeFree OpenCart Theme
  admin/view/template/theme/befree.twig
  catalog/controller/startup/befree.php
  catalog/controller/finder/finder.php
  catalog/model/finder/finder.php
  catalog/view/stylesheet/befree.css
  catalog/view/javascript/befree.js
  catalog/view/template/...
```

Settings are stored under the code `theme_befree`. The store setting `config_theme` is the code `befree`.

Admin labels and the home-page chrome are English. Product names, categories, and the rest of OpenCart follow the store language.

## Changes in 1.1.0

- Renamed from the single-store build to BeFree for redistribution.
- Removed the previous store's phone numbers, addresses, product claims, and named quotes from the defaults.
- Made one package cover OpenCart 4.0 and 4.1.
