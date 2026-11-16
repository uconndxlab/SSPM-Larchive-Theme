SSPM database integration is contained entirely in `public/themes/SSPM-Larchive-Theme`.

The homepage Blade view loads theme helpers from `src/bootstrap.php` and queries existing published, visible items through Laravel's existing Eloquent models. Cards use real item metadata, collection names, assigned terms, and media. The item Blade view prepares its own presentation data from the item supplied by the existing controller. No controller, route, application service, application parser, Composer configuration, database schema, or existing test changes are required.

Theme helpers normalize media paths, select primary recordings and resources, apply visibility when rendering attachments, and adapt existing SRT/OHMS parsers for transcript segments. Audio/video playback uses the existing `media.stream` route. Images and downloads use the configured public disk URLs; download links specify original filenames through the HTML `download` attribute. Media delivery authorization and byte-range behavior remain the responsibility of the existing Laravel application; this theme does not add endpoint-level authorization or new download routes.

Verification on September 16, 2026:

- 16 tests passed, 149 assertions: 10 theme integration tests plus the existing four theme-resolution and two media upload security tests. Integration tests use SQLite in memory and isolated storage.
- Headless Chrome against an isolated fixture database: search; combined category/language/duration filters; unknown durations; clear controls; refresh; empty results; grid/list switching; WAV playback with generic MIME metadata; seeking; recording selection; missing and failed playback; rejected play requests; overview/transcript/resource tabs; real WebM video playback and transcript segment seeking. No JavaScript runtime exceptions were observed.
- All 49 existing items rendered successfully with an unsaved administrator used solely for read-only template verification.
- JavaScript syntax checks and `git diff --check` passed. `git diff --exit-code -- app routes tests` confirmed no application, route, or existing test changes.

Run the theme integration and relevant existing regression tests from the Laravel root:

```sh
vendor/bin/phpunit -c phpunit.xml public/themes/SSPM-Larchive-Theme/tests tests/Feature/ThemeTest.php tests/Feature/MediaChunkUploadSecurityTest.php
```

Current data: two collections and all 49 items are drafts. There are no published homepage records or qualifying featured exhibitions. All 115 originals, including 40 WAV attachments, remain absent from the configured public disk. [missing-media.json](missing-media.json) lists their media IDs, item IDs, stored paths, and normalized paths. Database records and originals were not altered.

The existing Laravel `.gitignore` excludes `public/themes/*`, so these theme changes remain on disk and do not appear in ordinary Git status/diff output. Standalone `front-ends` prototypes and decorative assets were left unchanged. The pre-existing untracked `package-lock.json` was preserved. Nothing was staged, committed, pushed, or changed in Git configuration.
