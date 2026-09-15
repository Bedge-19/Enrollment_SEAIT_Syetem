---
name: Aura Radiant Dark
colors:
  surface: '#051424'
  surface-dim: '#051424'
  surface-bright: '#2c3a4c'
  surface-container-lowest: '#010f1f'
  surface-container-low: '#0d1c2d'
  surface-container: '#122131'
  surface-container-high: '#1c2b3c'
  surface-container-highest: '#273647'
  on-surface: '#d4e4fa'
  on-surface-variant: '#ddc1ae'
  inverse-surface: '#d4e4fa'
  inverse-on-surface: '#233143'
  outline: '#a48c7a'
  outline-variant: '#564334'
  surface-tint: '#ffb77d'
  primary: '#ffb77d'
  on-primary: '#4d2600'
  primary-container: '#ff8c00'
  on-primary-container: '#623200'
  inverse-primary: '#904d00'
  secondary: '#7bd0ff'
  on-secondary: '#00354a'
  secondary-container: '#00a6e0'
  on-secondary-container: '#00374d'
  tertiary: '#ffb95f'
  on-tertiary: '#472a00'
  tertiary-container: '#ec9700'
  on-tertiary-container: '#5a3700'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffdcc3'
  primary-fixed-dim: '#ffb77d'
  on-primary-fixed: '#2f1500'
  on-primary-fixed-variant: '#6e3900'
  secondary-fixed: '#c4e7ff'
  secondary-fixed-dim: '#7bd0ff'
  on-secondary-fixed: '#001e2c'
  on-secondary-fixed-variant: '#004c69'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#051424'
  on-background: '#d4e4fa'
  surface-variant: '#273647'
typography:
  display:
    fontFamily: Space Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.03em
  display-mobile:
    fontFamily: Space Grotesk
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Space Grotesk
    fontSize: 36px
    fontWeight: '600'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Space Grotesk
    fontSize: 26px
    fontWeight: '600'
    lineHeight: 34px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Space Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Space Grotesk
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 26px
  title-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.04em
  label-sm:
    fontFamily: JetBrains Mono
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.06em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 1rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system translates the luminous, energetic essence of its light predecessor into a high-performance, immersive dark environment. Designed for power users, developers, and modern enterprise professionals who demand prolonged visual comfort without sacrificing visual dynamism, this aesthetic balances deep structural restraint with radiant focal points.

The core philosophy merges **Modern Precision** with **Atmospheric Layering**:
- **Deep Slate Architecture**: Foundation layers sit in rich, calibrated dark slate and charcoal tones rather than flat absolute blacks, preventing harsh eye strain and preserving spatial dimension.
- **Electric Focal Accents**: The signature energetic amber-orange pierces through the dark canvas to deliver decisive affordances, immediate wayfinding, and critical state changes.
- **Micro-Luminescent Structural Lines**: Translucent hair-thin borders define containers cleanly against deep backdrops, delivering structural rigor and crisp modern clarity.

## Colors

The palette leverages a stepped scale of dark slate surfaces to construct visual depth, punctuated by an ultra-vibrant amber-orange primary accent.

### Color Tokens & Semantic Roles
- **Primary (`#ff8c00`)**: Reserved strictly for high-impact interactive states, primary action triggers, progress fills, and active indicators. On dark slate, this achieves an energetic, glowing presence without feeling neon.
- **Secondary (`#38bdf8`)**: A lucid sky cyan providing cool chromatic relief against warm primary highlights, applied to secondary metrics, informational notices, and badge accents.
- **Tertiary (`#f59e0b`)**: A golden-amber neighbor to primary, handling transitional warnings, pending indicators, and subtle attention hooks.
- **Neutral (`#94a3b8`)**: Cool slate supporting muted metadata, placeholder values, and neutral secondary icons.

### Surface Hierarchy
- **Canvas Base (`#0b0f17`)**: The deepest foundational layer for window frame or background viewport.
- **Surface Level 1 (`#111827`)**: The structural container tone for primary views, page bodies, and data grids.
- **Surface Level 2 (`#1e293b`)**: Raised cards, active list entries, and floating module cards.
- **Surface Level 3 (`#334155`)**: Modals, dropdown menus, and popovers.

### Contrast & Text Rules
- **Text Primary (`#f8fafc`)**: Crisp, 95% opacity off-white ensuring WCAG AAA legibility against all dark surfaces.
- **Text Secondary (`#94a3b8`)**: Muted metadata and supporting copy (minimum 4.5:1 ratio against Level 1 surfaces).
- **Text On Primary (`#0b0f17`)**: Deep obsidian text stamped directly onto vibrant `#ff8c00` fills to ensure stark readability.
- **Interactive Ghost Borders (`rgba(255, 255, 255, 0.08)`)**: Subtle white alpha borders providing definitive edge separation.

## Typography

The typographic hierarchy blends technical precision with modern, inviting structure:
- **Headlines (`Space Grotesk`)**: Provides an angular, technological cadence that makes marketing statements and screen titles feel forward-looking and crisp against deep obsidian backgrounds.
- **Body Text (`Plus Jakarta Sans`)**: Delivers rounded, ergonomic humanist clarity with generous aperture, counteracting optical crowding and text fatigue common in dark environments.
- **Metadata & Technical Accents (`JetBrains Mono`)**: Provides strict, predictable alignment for statistics, status counters, code references, and micro-labels.

### Implementation Guidelines
- Restrict uppercase transformations strictly to `label-sm` and badge indicators.
- In dark themes, avoid font weights below 400 (`Regular`) to prevent optical thinning caused by light text on dark pixels.

## Layout & Spacing

This design system uses a 12-column responsive fluid grid pinned to an 8pt layout rhythm, with a 4pt sub-grid for fine internal component alignments.

### Breakpoints & Responsive Behavior
- **Desktop (1280px+)**: 12-column layout, `gutter`: 1.5rem (24px), `margin`: 2rem (32px), maximum content container of 1440px centered on canvas.
- **Tablet (768px - 1279px)**: 8-column layout, `gutter`: 1rem (16px), `margin`: 1.5rem (24px). Dense dashboards reflow into stacked cards.
- **Mobile (Below 768px)**: 4-column layout, `gutter-mobile`: 1rem (16px), `margin-mobile`: 1rem (16px). Dual-column cards collapse to single-column full-width flows.

### Spacing Rules
- Never use outer element margins to dictate layout spacing; always control layout via grid gaps, auto-layout flex containers, and declared tokens (`space-*`).
- Reserve `space-xl` for sectional transitions, while keeping component interiors bounded between `space-sm` and `space-md` to preserve density and information clarity.

## Elevation & Depth

In this dark mode architecture, depth is established through **calibrated surface luminescence** and **crisp hairline containment**, avoiding thick muddy drop shadows that visually clutter deep dark UI.

### Elevation Architecture
1. **Level 0 (Base Canvas - `#0b0f17`)**: Ground plane. Completely flat, no borders.
2. **Level 1 (Inset Cards & Panes - `#111827`)**: Subdued panels. Border: `1px solid rgba(255, 255, 255, 0.06)`. No box shadow.
3. **Level 2 (Interactive Floating Cards - `#1e293b`)**: Floating UI containers. Border: `1px solid rgba(255, 255, 255, 0.1)`. Shadow: `0 4px 20px -2px rgba(0, 0, 0, 0.5)`.
4. **Level 3 (Overlays, Modals, Menus - `#334155`)**: Highest standard elevation. Border: `1px solid rgba(255, 255, 255, 0.16)`. Shadow: `0 12px 32px -4px rgba(0, 0, 0, 0.7)`.

### The Radiant Accent Glow
Primary active elements and focal triggers utilize a directional micro-glow:
- Primary Focused / Active: `box-shadow: 0 0 16px -2px rgba(255, 140, 0, 0.45);`
- Selected Item Rim: `border-color: rgba(255, 140, 0, 0.6);`
This produces the radiant optical illusion of a backlit interface.

## Shapes

The design system employs a **Rounded** shape language (`roundedness: 2`), offering a tactile, approachable geometry that offsets the austere severity of deep dark backgrounds.

### Corner Radius Mapping
- **Default / Base (`0.5rem` / 8px)**: Inputs, text areas, standard buttons, badge containers, list items.
- **Large (`1rem` / 16px)**: Dashboard cards, dialog panes, notification panels.
- **Extra Large (`1.5rem` / 24px)**: Floating action panels, global application drawers, hero containers.
- **Pill (`9999px`)**: Status chips, toggle switches, counter pills, and primary icon action caps.

## Components

### Buttons
- **Primary**: Background `#ff8c00`, text `#0b0f17` (bold), border none. Hover: background `#ffa029`, shadow `0 0 16px -2px rgba(255, 140, 0, 0.4)`. Active: scale 0.98.
- **Secondary**: Background `#1e293b`, text `#f8fafc`, border `1px solid rgba(255, 255, 255, 0.12)`. Hover: background `#334155`, border-color `rgba(255, 255, 255, 0.2)`.
- **Ghost**: Background transparent, text `#94a3b8`. Hover: text `#f8fafc`, background `rgba(255, 255, 255, 0.05)`.

### Chips & Badges
- **Active / Accent**: Background `rgba(255, 140, 0, 0.12)`, text `#ff8c00`, border `1px solid rgba(255, 140, 0, 0.35)`.
- **Neutral**: Background `rgba(255, 255, 255, 0.05)`, text `#94a3b8`, border `1px solid rgba(255, 255, 255, 0.08)`.
- Geometry: Full pill radius (`9999px`), internal horizontal padding `space-sm`, font token `label-sm`.

### Input Fields & Controls
- **Input Fields**: Background `#0f172a`, border `1px solid rgba(255, 255, 255, 0.12)`, text `#f8fafc`, placeholder `#64748b`. Radius `0.5rem`.
- **Input Focus State**: Border `1px solid #ff8c00`, shadow `0 0 0 3px rgba(255, 140, 0, 0.2)`.
- **Checkboxes & Radios**: Unchecked: `#0f172a` fill, `1px solid rgba(255, 255, 255, 0.2)` border. Checked: `#ff8c00` fill with `#0b0f17` glyph icon.

### Cards
- Container background `#111827`, border `1px solid rgba(255, 255, 255, 0.08)`, radius `1rem` (`rounded-lg`), padding `space-lg`.
- **Interactive Card Variant**: On hover, background shifts to `#1e293b`, border brightens to `rgba(255, 255, 255, 0.16)`, subtle elevation shadow applied.

### Lists
- Container padding 0; list row items separated by `1px solid rgba(255, 255, 255, 0.05)`.
- Hover state: Background `rgba(255, 255, 255, 0.03)` with left accent strip indicator in `#ff8c00` for active selection.

### Key Value Data Pairs (Specialized Product Component)
- Standardized pattern for metrics and properties: `label-sm` in `#64748b` uppercase stacked over a `title-md` or `headline-sm` value in `#f8fafc`. If status applies, append an inline 6px circular glowing indicator.