This is mirror of git@git.drupal.org:project/hidden_tab.git
https://www.drupal.org/project/hidden_tab

Extra tabs on entity view pages, with secret access link, mailer and credit system

This module makes it possible to add extra tabs to entity view pages (justs as core provides 'add' and 'edit' tabs). It's also possible to hide the extra tab, and make it accessible only via a secret per entity generated URI (and hence this module's name). Take google drive as an example where it creates a secret, long, hash generated URI to access the file.
Credit System

Instead of allowing users to visit the hidden tabs indefinitely, access may be limited to certain amount of visits via charging users with credits. Each visit to the page accounts for some credit. Credit charging may be configured per-ip and/or per-timespan where a revisit of the URI won't charge user with any additional credits. Credits can be provided and charged per-entity, per-user, per-bundle and per hidden tab or any combination of these.
Mailer System

Different mailers may be defined, so the hidden uri is regularly sent to certainusers. It could be the entity author or an email field in an entity or ...
Tab Layout

Tabs display layouts which are actually just twig templates, which in turn have regions filled with komponents, which are views, blocks or ...
Different templates may be provided via plugin system, with libraries (css and js) attached, or instead, simply an inline twig template may be used. Komponents are configurable per-bundle, per-user and per-entity via permission system. They are arranged in the same manner as blocks are.

Don't forget to enable views_embed_view in views settings!
Plugin System

Access control, komponents providers, mail discovery, templates and template context providers are all plugable sub-systems and may be swapped or extended by custom implementations. Although inline templating is possible for convinience, making writing a new module not strictly necessary.
Sample

You can find the sample module, per node analytics, here: Per Node Analytics

