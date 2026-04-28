# PMPro MCP + WordPress 6.9 Abilities API Roadmap

_Last updated: 2026-04-27_

## What we found

### 1) WordPress core now has an Abilities API (6.9+)
WordPress 6.9 introduced the Abilities API (`wp_register_ability()`, ability categories, and optional REST exposure), which is designed to make plugin functionality discoverable and executable by external systems, including AI clients.

### 2) WordPress has an official MCP bridge
The official `WordPress/mcp-adapter` project converts registered WordPress Abilities into MCP tools/resources/prompts so MCP clients can call them.

### 3) PMPro does not currently expose Abilities
PMPro has rich custom REST routes under `pmpro/v1` and report registration hooks/capabilities, but it does not currently register any WordPress Abilities or integrate directly with MCP.

## Recommendation

Use **WordPress Abilities API + MCP Adapter as the primary path**.

In other words: build a PMPro Abilities layer and let the official adapter expose those abilities to agent clients. This is the fastest way to become MCP-ready while staying aligned with WordPress core architecture.

A standalone PMPro MCP server can be a later phase only if we need advanced PMPro-specific transports/features not covered by the core adapter.

---

## Current PMPro building blocks we can reuse

- Existing PMPro REST routes for memberships, levels, orders, quick search, etc.
- Existing PMPro reports registry (`pmpro_registered_reports`) and report capabilities (`pmpro_reports`, `pmpro_reportscsv`, etc.).
- Existing PMPro capability model (`pmpro_edit_members`, `pmpro_orders`, `pmpro_reports`, ...).

These are ideal primitives for creating secure ability callbacks and permission callbacks.

---

## Proposed architecture

## Phase 0 — Feature gate + compatibility checks

- Add a feature gate option/filter, e.g. `pmpro_enable_abilities_api`.
- Only initialize when:
  - WordPress is 6.9+
  - `wp_register_ability()` exists
- Keep functionality no-op on older sites.

## Phase 1 — Register PMPro ability categories

On `wp_abilities_api_categories_init`, register PMPro categories:

- `pmpro-memberships`
- `pmpro-levels`
- `pmpro-orders`
- `pmpro-reports`
- `pmpro-discounts`
- `pmpro-members`

Each category should include clear descriptions so agents can choose tools correctly.

## Phase 2 — Register high-value read abilities first

On `wp_abilities_api_init`, register safe read-focused abilities with JSON Schema I/O:

- `pmpro/get-member` (by user ID/email)
- `pmpro/get-member-levels`
- `pmpro/list-levels`
- `pmpro/get-order`
- `pmpro/list-recent-orders`
- `pmpro/list-reports`
- `pmpro/get-report-summary` (memberships/sales/logins snapshots)

Design principle: ship readonly first for trust and easier review.

## Phase 3 — Add controlled write abilities

After read abilities are stable:

- `pmpro/change-membership-level`
- `pmpro/cancel-membership-level`
- `pmpro/create-discount-code`
- `pmpro/update-level`

Each write ability should enforce:

- strict schema validation
- PMPro capability checks
- idempotency guidance where practical
- audit logging hooks for observability

## Phase 4 — Map PMPro reports into agent-friendly outputs

Reports should return compact structured payloads rather than raw HTML tables:

- period start/end
- totals
- time-series buckets
- top segments (levels/codes)

Optional: keep a “detail URL” field back to wp-admin for human verification.

## Phase 5 — MCP adapter integration hardening

- Verify ability discovery and execution in MCP clients via official adapter.
- Ensure ability names/descriptions are optimized for tool selection by LLMs.
- Add allow/deny filters (per ability) for site owners.
- Document recommended auth path (Application Passwords + least-privilege service user).

---

## Suggested v1 ability shortlist

1. `pmpro/list-levels`
2. `pmpro/get-member`
3. `pmpro/get-member-levels`
4. `pmpro/get-order`
5. `pmpro/list-recent-orders`
6. `pmpro/get-memberships-report-summary`
7. `pmpro/get-sales-report-summary`
8. `pmpro/change-membership-level` (write)
9. `pmpro/cancel-membership-level` (write)

This set covers the most common “ask my site” agent use cases around memberships + reporting.

---

## Security model (non-negotiable)

- Permission callbacks must map to existing PMPro/WP capabilities.
- Never expose unrestricted member PII by default.
- Add explicit filters to redact sensitive fields from outputs.
- Add per-ability logging hooks (`before/after ability execute`) for audit trails.
- Prefer readonly defaults; require explicit opt-in for write abilities.

---

## Delivery plan

## Milestone A (1–2 sprints)

- Core scaffolding + category registration
- 5–7 readonly abilities
- unit/integration tests for schema and permission callbacks
- developer docs with MCP adapter setup

## Milestone B (1 sprint)

- write abilities
- logging + redaction filters
- hardening based on dogfooding feedback

## Milestone C (optional)

- evaluate whether a standalone PMPro MCP server still adds value beyond WordPress MCP adapter

---

## Open product decisions

1. Should write abilities be disabled by default?
2. Should report abilities return only aggregate stats or include row-level data?
3. Do we want a dedicated PMPro service-account role/capability preset for agent access?
4. Where should logs live (PMPro table vs WP logger abstraction)?

---

## Notes for implementation

- Prefer ability callbacks that call shared PMPro business logic (not duplicated SQL).
- Keep schemas strict and versionable.
- Use clear, stable ability names (`pmpro/*`) to avoid breaking MCP clients.
- Treat ability output as API contracts and cover with regression tests.
