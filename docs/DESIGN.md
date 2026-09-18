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
