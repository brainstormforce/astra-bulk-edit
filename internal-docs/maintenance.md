# Maintenance Guide

## When to Update These Docs

| Event | Docs to Update |
|-------|---------------|
| New meta option added | `apis.md` (meta keys table), `codebase-map.md` (method table), `ai-agent-guide.md` |
| New Astra Pro integration | `apis.md` (hooks), `product-vision.md` (scope), `ui-and-copy.md` (conditional UI) |
| Security fix | `troubleshooting.md`, `faq.md` (security section), `coding-standards.md` if patterns change |
| Astra version compat change | `architecture.md` (migration table), `glossary.md`, `troubleshooting.md` |
| Build process change | `README.md` (quick start), `coding-standards.md` (CI/CD), `onboarding.md` |
| New file/folder added | `codebase-map.md` |
| Major refactor | `architecture.md`, `codebase-map.md`, `ai-agent-guide.md` |

## How to Update

1. Make code changes first
2. Update affected doc files - check the table above
3. Update line numbers in `codebase-map.md` if methods shifted
4. Keep `apis.md` meta keys table in sync with `setup_bulk_options()`

## Style Guide

- Write in present tense ("The plugin uses..." not "The plugin will use...")
- Include file paths and line numbers where helpful
- Use tables for structured data
- Keep each doc between 500-1,500 words
- Derive content from actual code, never guess

## Verification

After updating docs, verify:
- [ ] All file paths mentioned still exist
- [ ] Line number references are still accurate
- [ ] Meta key lists match `setup_bulk_options()`
- [ ] Hook lists match constructor registrations
- [ ] No secrets or credentials included
- [ ] `internal-docs/` is still excluded from releases (check `.distignore` and `Gruntfile.js`)
