# Sprint 6 Progress — Addendum v2 (Bright Sea re-theme, light-only, new logo)

Tracks `plan2.md` only (the UI-refinement v2 slices). Original Sprint 6 tasks (T1–T12) are tracked in
`progress.md`. Update locally; fold into the sprint's closing commit (do not commit per task).

## Status legend
`[ ]` to do · `[~]` in progress · `[x]` done (committed) · `[!]` blocked / needs owner

## Locked decisions confirmed
- [x] Dark mode: **dropped (light-only)** — Option A (decision-guard). Infra kept, toggle hidden.
- [x] Direction: **white body + bright-sea accent** (Tokopedia/Shopee/JKTNotebook pattern).
- [x] Palette: **Bright Sea** — Sea `#13B5C4`, Sea-deep `#0A7180`, Ink `#062E36`, Coral `#FF6B5A`.
- [x] Fonts: wordmark **Plus Jakarta Sans ExtraBold** via `<text>`; body **Instrument Sans**.
- [x] Logo: **"Tiga Arus"** three-current whirlpool mark (hand-built SVG by Opus, NOT Gemini). Three
      wave arms in sea shades `#13B5C4 / #0E9AA6 / #0A7180` = Buyer/Seller/Driver, swirling into one
      central vortex = one account. Owner-chosen asymmetric arrangement (arms rotate 55/190/315).
- [x] Cadence: refine **one page at a time**.

## Logo asset
- [x] Final mark + lockup designed (SVG). Asymmetric "Tiga Arus" + 2-tone wordmark (SEA `#0A7180` /
      PEDIA `#13B5C4`). Iteration archive (v2/v3/v4 + opsi1–4) in session scratchpad.
- [x] **Placed (Opus, asset-only):** `public/favicon.svg` (mark) + `public/seapedia-logo.svg` (lockup).
      Favicon already linked in `resources/views/app.blade.php`.
- [ ] **R0 (Sonnet):** wrap lockup in `resources/js/components/brand/Logo.vue` (`variant="full|mark"`),
      use in header + landing. Optional tighter mark variant for 16–32px favicon.

## Slices (in order)
- [x] **R0 · Tokens + logo + dark-mode removal** — `feat(ui): adopt bright-sea light-only palette and
      new logo` (commit 591cb82). Rewrote `:root`, added `--brand`, Logo.vue, force light, sweep
      emerald → primary in driver/seller pages.
- [x] **R1 · Landing + public reviews** — `feat(ui): redesign landing hero with sea gradient,
      animations, and navbar search` (commit 011df39). Sea gradient hero, rotating mark, fade-up,
      parallax (desktop=mouse, mobile=scroll), wave divider, promo bar, search bar.
      - [!] Hero image + product photos: photos pendemo perlu diisi secara manual (placeholder ok).
- [x] **R2 · Auth (login / register / role-select)** — `feat(ui): upgrade auth layout left panel to
      sea gradient with rotating mark` (commit f18c300).
- [x] **R3 · Catalog + product detail + store** — `feat(ui): fix catalog and store product cards to
      1:1 aspect ratio` (commit 2c8f1b7). Fixed aspect-video → aspect-square, hover polish.
- [x] **R4 · Seller pages** — clean, no hardcoded colors; tokens auto-applied. Included in R4–R6 commit.
- [x] **R5 · Buyer pages** — `style(ui): remove dead dark: variants and migrate neutral tokens to
      semantic` (commit f3203d5). Cleaned wallet, cart, topup dark: dead code.
- [x] **R6 · Driver + Admin** — consistency sweep included in above commit (LanguageTabs, components).

## Known gaps / notes
- Playwright visual-QA intentionally skipped (memory: no-playwright-sprint5-onward) — verify manually
  at 1920×1080 (+ 360/768/1280 sanity).
- Dark-mode `.dark` token blocks remain in `app.css` (dead, kept for easy re-enable).

## Logo — final approach (superseding the earlier Gemini "S/book" idea)
Logo dibuat tangan via SVG oleh Opus (bukan Gemini — eksperimen Gemini gagal karena minta outline
wordmark ke `<path>` → output meledak). Konsep final = **"Tiga Arus"** (three-current whirlpool):
tiga lengan ombak (Buyer/Seller/Driver) berputar dari satu pusaran (satu akun, tiga peran). Wordmark
pakai `<text>` Plus Jakarta Sans 800 (SEA `#0A7180` / PEDIA `#13B5C4`), bukan outline.
- Aset live: `public/favicon.svg`, `public/seapedia-logo.svg`.
- Arsip iterasi (v2/v3/v4, opsi1–4, preview HTML) ada di scratchpad sesi brainstorming.
- Hero-prompt & spec foto produk: lihat `plan2.md` § "Aset visual — hero & foto produk".
