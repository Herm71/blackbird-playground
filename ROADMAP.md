# Blackbird Sandbox — Remediation Roadmap

Audit date: 2026-09-04 · Plugin version: 0.1.0 · Baseline commit: 96b72ef

Status of the codebase: all plugin logic lives in a single 223-line `plugin.php`.
There is no test harness, PHPCS reports 194 errors, and the built block in
`build/` is never registered with WordPress.

Every finding below is tracked as a GitHub issue and every phase as a
milestone. This document holds the reasoning; the issues hold the working
state. When the two disagree, the issues are current.

## Findings

Severity: **S1** breaks behavior or exposes users · **S2** silently wrong or
unreachable code · **S3** hygiene, maintainability, convention.

| ID | Issue | Sev | Finding | Location |
|----|-------|-----|---------|----------|
| BB-01 | [#6][BB-01] | S1 | Shortcode output concatenates ACF values with no escaping. `editorial_style_item` (ACF `text`) and `editorial_style_definition` (ACF `wysiwyg`) go straight into HTML. Any editor with `edit_posts` can inject script. | `plugin.php:81`, `plugin.php:121` |
| BB-02 | [#7][BB-02] | S1 | `wp_reset_postdata()` is placed *after* `return` — unreachable. `WP_Query` leaves global `$post` clobbered, corrupting everything rendered after the `[style-archive]` shortcode. | `plugin.php:129` |
| BB-03 | [#8][BB-03] | S1 | `search_template` is a filter but is registered with `add_action`. The callback runs and its return value is discarded, so the template override never takes effect. | `plugin.php:179` |
| BB-04 | [#9][BB-04] | S2 | The `blackbird/birdblocks` block is compiled to `build/` but no `register_block_type()` call exists anywhere. The block cannot be inserted. `src/` and `build/` are dead weight. | no PHP registration |
| BB-05 | [#10][BB-05] | S2 | Text domain mismatch. Header declares `birdblocks`; all six `__()` calls pass `ucscgiving`. No string will ever translate. | header `:11` vs `:147-155` |
| BB-06 | [#11][BB-06] | S2 | `filemtime()` called with no `file_exists()` guard. Emits a PHP warning and a bad cache-buster if `style.css` is absent from a build. | `plugin.php:31` |
| BB-07 | [#12][BB-07] | S3 | No `ABSPATH` guard. `plugin.php` executes on direct HTTP request. | `plugin.php` top |
| BB-08 | [#13][BB-08] | S3 | `define()` calls are unguarded and named `UCSCCOMMS_*` in a plugin named Blackbird — leftover from `ucsc-giving-functionality`. Redefinition notice if that plugin is co-installed. | `plugin.php:17-18` |
| BB-09 | [#14][BB-09] | S3 | `custom_filter_posts()` is an unprefixed global function on `pre_get_posts`. High collision risk. | `plugin.php:181` |
| BB-10 | [#15][BB-10] | S3 | Three prefixes coexist: `blackbird_playground_`, `ucsccomms_`, `ucscgiving_`, plus `bb_`. | throughout |
| BB-11 | [#16][BB-11] | S3 | PHPCS: 194 errors, 10 warnings across `plugin.php` and `templates/`. 151 auto-fixable. | repo-wide |
| BB-12 | [#17][BB-12] | S3 | `templates/funds-search.php` opens `<?PHP`, has an unbalanced extra `</div>`, and carries commented-out dead code. | `templates/funds-search.php` |
| BB-13 | [#5][BB-13] | S3 | No test harness. `composer.json` has no PHPUnit; no `tests/` directory exists. | repo-wide |

### Explicitly not a finding

The `$wpdb` query in `custom_filter_posts()` is correctly built: it uses
`$wpdb->prepare()` with `$wpdb->esc_like()` on the user term. The `%` characters
in the meta-key patterns are intentional wildcards matching ACF repeater row
indices. Do not "fix" this.

## Process

### Constraint: BB-13 gates everything

`AGENTS.md` requires a test commit alongside each implementation commit. That is
currently impossible — there is nothing to commit a test to. Phase 0 exists to
make the rest of the roadmap compliant with our own conventions. Do it first or
accept that every later phase violates the standard.

### Ordering rationale

Behavioral fixes land **before** the formatting sweep. Running `phpcbf` first
would rewrite 151 lines and bury the security diffs in whitespace noise, making
review and `git blame` useless. The mechanical reformat goes last, isolated in
its own commit that touches no logic.

### Phases

**Phase 0 — Test harness** (blocks all others) · [milestone][M0]
Add PHPUnit + WP test scaffolding to `composer.json`, create `tests/`, wire a
`composer test` script. Land one smoke test proving the harness runs.
Closes [BB-13].

**Phase 1 — Security and correctness** (S1) · [milestone][M1]
One commit per finding, each paired with a regression test.
- [BB-01]: `esc_html()` on `$azItem`, `wp_kses_post()` on `$azDef` (wysiwyg must
  keep its markup — do not use `esc_html` here). Both call sites.
- [BB-02]: move `wp_reset_postdata()` above the `return`.
- [BB-03]: `add_action` → `add_filter`. Verify `locate_template('')` returns what
  the override actually needs; it currently looks suspect.

**Phase 2 — Plugin hygiene** (S2/S3) · [milestone][M2]
- [BB-05] text domain, [BB-06] `file_exists()` guard, [BB-07] `ABSPATH` guard,
  [BB-08] constant rename + `defined()` guards, [BB-09]/[BB-10] settle on the single
  `blackbird_` prefix.
- [BB-08] and [BB-09] are renames: grep the wider site for external callers before
  committing.

**Phase 3 — Block registration** · [milestone][M3]
- [BB-04]: `register_block_type_from_metadata( __DIR__ . '/build' )` on `init`.
  Decide first whether the block is still wanted — if not, delete `src/` and
  `build/` and drop the `@wordpress/scripts` toolchain instead. Cheaper outcome.

**Phase 4 — Formatting sweep** · [milestone][M4]
- [BB-11], [BB-12]: `composer lint-fix`, then hand-fix the ~43 remaining. Single
  mechanical commit, no logic changes. Also fix the `<?PHP` tag and stray
  `</div>`.
- Note `.phpcs.xml.dist` excludes `/docroot/wp-content/plugins/*`, which would
  suppress this plugin entirely if the repo is ever mounted inside a site.
  Re-scope the ruleset.

**Phase 5 — Restructure** (optional) · [milestone][M5]
`composer.json` already declares PSR-4 `Jason\Playground\` → `src/`, but `src/`
holds only block JS. Either split `plugin.php` into a thin bootstrap plus
`includes/` with a hook loader and use the autoloader, or drop the unused
PSR-4 block. Do not leave it half-wired. Tracked as [#18][PH5].

### Per-change workflow

1. Branch off `main` per issue: `fix/bb-01-escape-shortcode-output`.
2. Atomic conventional commits, one logical change each, subject under 72 chars,
   imperative mood. Body explains *why*.
3. Test commit accompanies each implementation commit.
4. `composer lint` and the test suite pass before opening a PR. Note that
   `composer lint` does not currently execute at all — see [BB-11].
5. Draft PR only. Description covers what changed (bullets), why, how to test,
   and any breaking change. Close the issue from the PR body (`Closes #6`) so
   the milestone tracks completion.

### Verification per phase

- Plugin activates with no fatals or notices.
- `[style-definition]` and `[style-archive]` render, and content *after*
  `[style-archive]` on the same page still renders correctly ([BB-02] regression).
- A `<script>` payload saved into an ACF style field renders inert ([BB-01]).
- Style Guide search returns filtered results ([BB-03]).
- `composer lint` clean.

[BB-01]: https://github.com/Herm71/blackbird-playground/issues/6
[BB-02]: https://github.com/Herm71/blackbird-playground/issues/7
[BB-03]: https://github.com/Herm71/blackbird-playground/issues/8
[BB-04]: https://github.com/Herm71/blackbird-playground/issues/9
[BB-05]: https://github.com/Herm71/blackbird-playground/issues/10
[BB-06]: https://github.com/Herm71/blackbird-playground/issues/11
[BB-07]: https://github.com/Herm71/blackbird-playground/issues/12
[BB-08]: https://github.com/Herm71/blackbird-playground/issues/13
[BB-09]: https://github.com/Herm71/blackbird-playground/issues/14
[BB-10]: https://github.com/Herm71/blackbird-playground/issues/15
[BB-11]: https://github.com/Herm71/blackbird-playground/issues/16
[BB-12]: https://github.com/Herm71/blackbird-playground/issues/17
[BB-13]: https://github.com/Herm71/blackbird-playground/issues/5
[M0]: https://github.com/Herm71/blackbird-playground/milestone/1
[M1]: https://github.com/Herm71/blackbird-playground/milestone/2
[M2]: https://github.com/Herm71/blackbird-playground/milestone/3
[M3]: https://github.com/Herm71/blackbird-playground/milestone/4
[M4]: https://github.com/Herm71/blackbird-playground/milestone/5
[M5]: https://github.com/Herm71/blackbird-playground/milestone/6
[PH5]: https://github.com/Herm71/blackbird-playground/issues/18
