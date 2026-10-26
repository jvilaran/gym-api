# Permissions & Autonomy

This file records the standing permissions the user has granted Kiro for this
project. It is always included as context.

## Granted autonomy

During development of planned tasks, Kiro has full autonomy to make changes
without asking for per-change approval. This includes:

- Creating, editing, moving, and deleting source files, tests, config, and docs.
- Running local, non-destructive commands: builds, linters, formatters,
  `composer` / `npm` installs, `php artisan` commands (including
  `migrate`, `migrate:fresh`, `db:seed`, `test`).
- Recreating the local SQLite database with `migrate:fresh --seed` when needed
  for verification. The local dev database is disposable.
- Refactoring to keep the codebase consistent with the existing architecture
  (Controller -> FormRequest -> DTO -> Service).

Kiro should proceed through tasks end to end without pausing for confirmation on
routine development actions.

## Always ask first

Kiro must stop and ask for explicit approval before:

- `git commit` or `git push` (any operation that writes to git history or a
  remote).
- Changing git configuration.
- Destructive git operations (force push, `reset --hard`, `clean -fd`,
  branch deletion).
- Deleting data or files outside the project workspace.
- Modifying production configuration or anything that affects a live/remote
  environment.
- Adding paid/external API credentials or enabling real third-party AI
  providers (default stays on the local `fake` driver).

## Communication

- Reply to the user in English.
- In-app content (API response messages, seed data) stays in Spanish, matching
  the existing codebase.
