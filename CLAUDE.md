@AGENTS.md

# Blackbird Sandbox

A single-plugin WordPress repo. The repo root **is** the plugin directory —
there is no `wp-content/plugins/` nesting. `plugin.php` is the only PHP entry
point and currently holds all plugin logic (~223 lines, procedural, no classes).

## Outstanding work

[ROADMAP.md](ROADMAP.md) is the authoritative backlog: findings BB-01 through
BB-13, phased, with an ordering rationale. Read it before changing `plugin.php`
— most of what looks broken is already catalogued there.

Two things in it that are easy to get wrong:

- The `$wpdb->prepare()` query in `custom_filter_posts()` is **correct**. The
  `%` in the meta-key patterns are deliberate wildcards matching ACF repeater
  row indices. Do not "harden" it.
- Behavioral fixes land before the `phpcbf` sweep. Reformatting first buries
  the security diffs in 151 lines of whitespace noise.

## Hard dependency: ACF

The plugin does not function without Advanced Custom Fields. `plugin.php` calls
`have_rows()`, `get_sub_field()`, and filters `acf/settings/save_json` /
`acf/settings/load_json` at the top level with no `function_exists()` guard.

`acf-json/` is the source of truth for the content model, synced from the ACF
admin UI:

| File | Defines |
|------|---------|
| `post_type_685d7e97c87c6.json` | `a_z_style_guide` post type |
| `post_type_685d82049f8a5.json` | `s_m_directory` post type |
| `group_5a1f2f21b2d3c.json` | `style_definitions` repeater → `editorial_style_item` (text), `editorial_style_definition` (wysiwyg) |
| `group_5a0373f2dec67.json` | "Social Media" — `social_media_property` repeater, bound to `s_m_directory` |

Renaming a field in the ACF UI rewrites these JSON files but **not** the PHP.
The shortcodes and the raw SQL in `custom_filter_posts()` hardcode
`style_definitions_%_editorial_style_item` and
`style_definitions_%_editorial_style_definition`. Field renames break search
silently.

`s_m_directory` and `social_media_property` are referenced nowhere in PHP or JS
— that half of the content model is orphaned. Its field group also carries a
stale location rule for a `sm-directory` post type that no longer exists.

## Public surface

- `[style-definition]` — renders the current post's `style_definitions` rows.
- `[style-archive]` — renders every `a_z_style_guide` post's rows.
- `core/search` block variation `styleguide-search`, scoped to `a_z_style_guide`.
- `pre_get_posts` filter rewriting Style Guide searches to a meta-value lookup.

## Traps

**`composer lint` does not run.** The script is `./vendor/bin/phpcs` with no
path, and `.phpcs.xml.dist` declares no `<file>`, so PHPCS exits with "You must
supply at least one file or directory." To actually lint:

```bash
./vendor/bin/phpcs --extensions=php plugin.php templates/   # 194 errors, 10 warnings
```

Do not run bare `phpcs .` — it walks `build/` and `src/` JS and reports 959
errors, drowning the PHP signal.

**`.phpcs.xml.dist` is inherited from another project.** It sets `text_domain`
to `ucsc-2022`, the global prefix to `ucsc`, and excludes
`/docroot/wp-content/plugins/*` — which would suppress this plugin entirely if
the repo were ever mounted inside a site. None of those values match this
plugin (BB-11).

**PSR-4 is declared but unused.** `composer.json` maps `Jason\Playground\` →
`src/`, but `src/` contains only block JS. There are no PHP classes and nothing
is autoloaded. Do not assume the autoloader is wired.

**`build/` is gitignored and untracked.** Run `npm run build` to produce it. The
compiled `blackbird/birdblocks` block is never passed to `register_block_type()`
in PHP, so it cannot be inserted in the editor regardless (BB-04).

**`.gitignore` is written for a full site root**, with `wp-content/*` rules that
match nothing here. Leftover; ignore it.

**Prefixes are inconsistent by history**, not by design: `blackbird_playground_`,
`ucsccomms_`, `ucscgiving_`, `bb_`, plus the unprefixed `custom_filter_posts()`.
The roadmap settles on `blackbird_` (BB-09, BB-10). Renames are cross-repo —
grep the wider site for callers before committing one.

**Versions disagree**: plugin header `0.1.0`, `package.json` `1.0.0`,
`CHANGELOG.md` last entry `1.0.0 (2022-05-18)`.

## Environment

There is **no test harness** — no PHPUnit in `composer.json`, no `tests/`.
AGENTS.md requires a test commit alongside each implementation commit, which is
impossible until ROADMAP Phase 0 lands. Phase 0 gates every other phase.

There is **no local WordPress environment** — no wp-env, Docker, DDEV, or Lando
config. Integration tests against real `WP_Query` / `pre_get_posts` behavior
need one stood up first.

Local PHP is 8.5; the plugin header claims `Requires PHP: 7.0`. WPCS is pinned
at `^2.3`, which predates PHP 8 sniffs.

## Commands

```bash
npm run build          # compile src/ -> build/
npm run start          # watch mode
npm run zip            # build + wp-scripts plugin-zip
composer lint-fix      # phpcbf --extensions=php . — passes a path, so unlike
                       # `composer lint` it actually runs. Rewrites in place.
```
