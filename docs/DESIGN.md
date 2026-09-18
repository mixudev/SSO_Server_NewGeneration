---
version: alpha
name: Mixu SSO Render Dashboard
description: Precise identity infrastructure UI with paper-white surfaces, hairline borders, and restrained plasma violet accents.
colors:
  primary: "#8A05FF"
  secondary: "#4D4D4D"
  neutral: "#FFFFFF"
  heading: "#0D0D0D"
  border: "#E3E3E3"
  surface-muted: "#F4F0FF"
  action: "#0D0D0D"
  success: "#006D4C"
  warning: "#D67F2E"
  danger: "#B4232F"
  dark-surface: "#211B29"
typography:
  display:
    fontFamily: Inter
    fontSize: 4rem
    fontWeight: 300
    lineHeight: 1.05
    letterSpacing: "-0.025em"
  heading:
    fontFamily: Inter
    fontSize: 2rem
    fontWeight: 300
    lineHeight: 1.1
    letterSpacing: "-0.01em"
  body:
    fontFamily: Inter
    fontSize: 1rem
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: JetBrains Mono
    fontSize: 0.6875rem
    fontWeight: 500
    lineHeight: 1.38
    letterSpacing: "0.02em"
  technical:
    fontFamily: JetBrains Mono
    fontSize: 0.75rem
    fontWeight: 400
    lineHeight: 1.43
    letterSpacing: "0.02em"
rounded:
  sm: 2px
  full: 9999px
spacing:
  xs: 8px
  sm: 12px
  md: 16px
  lg: 24px
  xl: 32px
  section: 80px
components:
  button-primary:
    backgroundColor: "{colors.action}"
    textColor: "{colors.neutral}"
    rounded: "{rounded.sm}"
    padding: 8px 12px
  button-primary-hover:
    backgroundColor: "{colors.primary}"
  input:
    backgroundColor: "{colors.neutral}"
    textColor: "{colors.heading}"
    rounded: "{rounded.sm}"
    padding: 10px 12px
  table:
    backgroundColor: "{colors.neutral}"
    textColor: "{colors.secondary}"
    rounded: "{rounded.sm}"
  status-success:
    backgroundColor: "#DFFeed"
    textColor: "{colors.success}"
    rounded: "{rounded.full}"
---

## Overview

The dashboard is a server-rendered Laravel Blade control plane for identity infrastructure. Its visual language is an engineering document: white space, precise alignment, 1px structural borders, and typography-led hierarchy. Tailwind CSS v4 is the only component styling layer.

## Colors

Use `#FFFFFF` for the page and component surfaces, `#0D0D0D` for headings and primary actions, `#4D4D4D` for body text, and `#E3E3E3` for borders. Reserve `#8A05FF` for focus states, active navigation, step markers, and small emphasis. Do not use violet as a large CTA fill. Dark mode uses deep violet-neutral surfaces from the dashboard theme tokens while preserving semantic roles.

## Typography

Inter is the available substitute for Roobert and PPNeueMontreal. Use light Inter for headings and regular or medium Inter for interface text. Use JetBrains Mono only for technical identifiers, compact labels, status metadata, and code. Labels are uppercase with positive tracking; body copy remains left-aligned and readable.

## Layout

Use a centered content width of 1200px, 24px component padding, 16–20px element gaps, and 80px section rhythm. Prefer grid and flex utilities over custom layout CSS. Use responsive Tailwind utilities for mobile stacking and horizontal table scrolling.

## Elevation & Depth

Cards and tables use borders and surface contrast, not elevation. Do not add drop shadows to normal content. Overlays and menus may use a restrained tokenized shadow for separation. Avoid backdrop blur on dense dashboard screens.

## Shapes

Rectangular controls, cards, inputs, buttons, and tables use a 2px radius. Status badges, profile triggers, secure labels, and other explicitly pill-shaped controls use `rounded-full`. Never apply large rounded corners to ordinary panels.

## Components

- Primary buttons are dark with white text; secondary buttons are bordered and surface-colored.
- Inputs use `border-[var(--dash-border)]`, theme surfaces, 2px radius, and violet focus rings.
- Tables use compact 12–14px content, mono uppercase headers, hairline row dividers, and horizontal overflow on small screens.
- Status badges use explicit text plus color; color must not be the only security signal.
- Icon-only actions must have an accessible label and visible focus state.
- Modals and alerts use the shared components and theme tokens; destructive actions require explicit confirmation.
- Components are Blade-first and styled with Tailwind utilities. Dashboard CSS is limited to tokens, layout primitives, and interaction geometry.

## Do's and Don'ts

- Do reuse existing Blade components before adding a new one.
- Do use semantic dashboard tokens through Tailwind arbitrary values instead of hardcoded legacy palettes.
- Do keep light and dark themes semantically equivalent.
- Do keep accessibility labels, keyboard focus, and validation messages.
- Don't use inline styles, CDN UI frameworks, or a second component styling system.
- Don't use `rounded-none`, generic slate/zinc color palettes, or shadow-heavy cards in dashboard components.
- Don't communicate authentication or security state by color alone.

## Dashboard & Feature Page Design System

Feature pages must be designed around the user's job and the meaning of the data, not around a fixed "title + create button + table" template. A page may use a profile hero, summary, KPI cards, contextual filters, quick actions, activity, insights, guided steps, empty states, or a table only when each element helps the user understand or complete the task.

### Page composition

Use this decision order before adding UI:

1. Identify the primary user goal and the safest next action.
2. Identify the most important context the user needs before acting.
3. Group information into purposeful sections with clear hierarchy.
4. Add supporting metrics, activity, filters, or insights only when they reduce decision effort.
5. Assign each action to one canonical page or component.

Every page should have an intentional flow:

```text
Context → primary decision → focused action → result or next step
```

Do not repeat the same action in multiple cards, page headers, footers, and related pages. If an action is available in a detail page, summary pages should link to that page rather than render a second control. Destructive actions belong beside the resource they affect and require confirmation.

### Profile and security pages

Profile pages may use a full-width contextual banner, followed by an interactive avatar or fallback avatar icon, then a responsive two-column information area. The narrower column contains identity facts; the wider column contains account details and security controls. Use card headers for title, status, and explanation; use card bodies for information; use card footers for the card's single primary action group.

Security settings should be consolidated into one understandable security center. Avoid duplicate buttons across the profile overview and security center. The overview shows status and one link to management; the security center owns setup, removal, reset, and session actions.

For setup flows, keep the user's attention on one decision at a time. Two-factor setup uses a left QR panel and a right verification panel. The verification panel contains one single code input, clear expiry or one-time-use guidance, and one confirmation action. Passkey registration opens the shared `app-modal`, asks for a device name, and requires explicit confirmation before invoking WebAuthn. Password reset opens the shared `app-modal`; it may collect the current password and new password when changing the password, and provides one clear action for sending a reset link to the account email. Never render secrets, recovery codes, WebAuthn payloads, password hashes, or reset tokens in HTML, logs, or validation output.

### Card system

Cards are product surfaces, not decorative containers. Use a consistent structure:

- Header: title, short context, status, and optional leading icon.
- Body: the information or interaction required for the card's purpose.
- Footer: one primary action group, secondary navigation, or a concise timestamp/source.

Use the existing dashboard card component patterns before creating a new component. Keep headers and footers visually distinct with spacing or a hairline divider. Do not create nested cards unless the inner grouping represents a genuinely different object. A card should not contain two competing primary actions.

### Visual hierarchy and page character

Allow each feature to have visual character while preserving the token system. Prefer contextual compositions over repeated grids:

- identity pages: banner, avatar, identity facts, security posture;
- security pages: protection status, setup guidance, device list, recent activity;
- registry pages: health summary, lifecycle state, filters, table, and useful empty state;
- workflow pages: progress, current step, validation summary, review, and completion state.

Use restrained motion for state changes, modal entry, focus feedback, loading transitions, and successful completion. Use hover states, iconography, subtle patterns, or illustrations only when they clarify interaction or orientation. Do not add decoration that competes with security, data density, or the primary action.

### Tables and forms

Tables must communicate resource state and support a task. Include useful status, ownership, timestamps, or lifecycle context when available; provide responsive horizontal scrolling on narrow screens; and provide a purposeful empty state with the next safe action. Forms should be grouped by intent, use explicit labels and server-side validation, preserve entered non-secret values after errors, and explain irreversible or security-sensitive actions before confirmation.

### State design

Every interactive feature must define these states before implementation:

- Loading: show local progress without shifting the layout unexpectedly.
- Empty: explain why there is no data and provide one safe next action when applicable.
- Error: identify what failed, preserve safe input, and offer recovery without exposing internals.
- Success: confirm the completed action and show the next relevant state.
- Confirmation: state the exact consequence and require an intentional choice for destructive or security-sensitive actions.
- Disabled: explain why the action is unavailable; do not render dead links as if they work.

### Responsive, accessible, and performant behavior

Design mobile first. Stack two-column layouts at the existing responsive breakpoint, keep primary actions reachable, and ensure dialogs fit within the viewport with keyboard scrolling. Every icon-only control needs an accessible name. Every modal needs focus management, Escape/backdrop behavior, visible focus, and a labeled dialog. Do not rely on color alone for status or validation. Respect reduced-motion preferences. Avoid client-side JavaScript when semantic HTML, CSS, or server-rendered state is sufficient.

### SaaS quality gate

Before accepting a feature page, verify:

- the page has a clear purpose and one obvious primary action;
- information hierarchy matches the user's decision;
- repeated actions have one canonical owner;
- card headers, bodies, and footers are consistent;
- loading, empty, error, success, confirmation, and disabled states are designed;
- light and dark themes preserve contrast and meaning;
- keyboard, screen-reader, mobile, and reduced-motion behavior are usable;
- tables and forms feel integrated into the product flow;
- no secrets or implementation details leak into the UI;
- the page adds no unnecessary visual element or dependency.

## Do's and Don'ts — Feature Pages

- Do design from the feature's purpose and data model.
- Do reuse `x-dashboard.*`, `x-app-modal`, and existing card/action patterns.
- Do place security setup and destructive actions in one canonical management surface.
- Do use contextual summaries and activity when they improve decisions.
- Do keep animation subtle, brief, and optional.
- Don't copy the same CRUD scaffold onto every page.
- Don't duplicate an action across overview, detail, and security pages.
- Don't use a modal for content that requires a full workflow, navigation, or substantial explanation.
- Don't hide validation, error, or disabled reasons behind color alone.
- Don't add visual effects, illustrations, or dependencies without a usability benefit.
