---
name: Aura Radiant
colors:
  surface: '#f8f9fa'
  surface-dim: '#d9dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f5'
  surface-container: '#edeeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#564334'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f2'
  outline: '#897362'
  outline-variant: '#ddc1ae'
  surface-tint: '#904d00'
  primary: '#904d00'
  on-primary: '#ffffff'
  primary-container: '#ff8c00'
  on-primary-container: '#623200'
  inverse-primary: '#ffb77d'
  secondary: '#5f5e5e'
  on-secondary: '#ffffff'
  secondary-container: '#e2dfde'
  on-secondary-container: '#636262'
  tertiary: '#00658f'
  on-tertiary: '#ffffff'
  tertiary-container: '#00b5fc'
  on-tertiary-container: '#004360'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdcc3'
  primary-fixed-dim: '#ffb77d'
  on-primary-fixed: '#2f1500'
  on-primary-fixed-variant: '#6e3900'
  secondary-fixed: '#e5e2e1'
  secondary-fixed-dim: '#c8c6c5'
  on-secondary-fixed: '#1c1b1b'
  on-secondary-fixed-variant: '#474746'
  tertiary-fixed: '#c7e7ff'
  tertiary-fixed-dim: '#85cfff'
  on-tertiary-fixed: '#001e2e'
  on-tertiary-fixed-variant: '#004c6c'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  display-lg:
    fontFamily: Work Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Work Sans
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Work Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Work Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Work Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Work Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Work Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-sm:
    fontFamily: Work Sans
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 48px
  xl: 80px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 64px
---

## Brand & Style

The design system is centered on clarity, optimism, and functional efficiency. It targets a broad user base that values speed and transparency, such as in productivity tools, educational platforms, or simple financial dashboards.

The style is **Modern Minimalism** with a focus on high-energy accents. By combining vast amounts of white space with a single, high-intensity vibrant orange, the UI directs the user's attention to primary actions without visual clutter. The emotional response should be one of "clean energy"—professional yet approachable, stripped of unnecessary ornamentation to prioritize the user's tasks.

## Colors

This color palette relies on high-contrast pairings to ensure maximum legibility and accessibility.

- **Primary (Vibrant Orange):** Used exclusively for high-priority calls to action, active states, and critical data highlights. 
- **Neutral (Light Gray):** Employed for surface backgrounds, subtle borders, and secondary containers to provide depth without introducing new hues.
- **Text (Off-Black/Dark Gray):** Pure black is avoided to reduce eye strain; #1A1A1A is used for primary headings to maintain a sharp, professional contrast against the white background.
- **Success/Error:** While not in the primary variables, these should utilize a standard green/red but at a lower saturation than the primary orange to prevent visual competition.

## Typography

The design system utilizes **Work Sans** for all typographic needs. It is chosen for its professional, grounded feel and exceptional legibility at small sizes. 

- **Headlines:** Use a bold weight with slightly tighter letter spacing to create a strong visual anchor.
- **Body Text:** Standard weight with generous line height (1.5x) to facilitate scanning and reading.
- **Labels:** Use medium or semi-bold weights to distinguish interactive metadata from static content.
- **Contrast:** Ensure all text on white backgrounds maintains at least a 4.5:1 contrast ratio, with primary orange reserved for interactive text elements only.

## Layout & Spacing

This design system uses a **Fluid Grid** approach based on an 8px square rhythm.

- **Desktop:** 12-column grid with 24px gutters. Wide margins (64px) ensure content remains centered and readable.
- **Mobile:** 4-column grid with 16px margins. 
- **Rhythm:** Spacing between distinct sections should be large (lg or xl) to emphasize the minimalist, "airy" feel. Internal component spacing should use sm or md units. 
- **Alignment:** All elements should snap to the 8px grid to maintain visual mathematical harmony.

## Elevation & Depth

To maintain a clean and flat aesthetic, the design system avoids heavy shadows. 

- **Tonal Layers:** Depth is primarily created through color. Backgrounds are #FFFFFF, while "container" elements (like dashboard widgets) use #F8F9FA.
- **Shadows:** Use only one level of shadow for floating elements (like dropdowns or modals). This should be an "Ambient Shadow": `0px 4px 20px rgba(0, 0, 0, 0.05)`.
- **Borders:** Subtle 1px borders in #E9ECEF are preferred over shadows for defining card boundaries.

## Shapes

The shape language is **Rounded**, creating a friendly and modern interface. 

- **Standard Elements:** Buttons, input fields, and small cards use a 0.5rem (8px) radius.
- **Large Containers:** Dashboard widgets and main content areas use `rounded-lg` (16px) to soften the layout.
- **Icons:** Should follow a similar soft-corner aesthetic, avoiding sharp 90-degree caps or joins.

## Components

- **Buttons:** 
    - *Primary:* Solid Vibrant Orange with white text. High-padding (12px 24px).
    - *Secondary:* White background with an Orange border and Orange text.
- **Input Fields:** 1px light gray border, 8px corner radius. On focus, the border transitions to Vibrant Orange with a 2px thickness. Labels are always placed above the field in `label-md`.
- **Chips:** Small, rounded-pill shapes used for status or filtering. Use light orange backgrounds (#FFF5E6) with dark orange text for high-visibility states.
- **Dashboard Widgets:** Pure white cards with a 1px #F8F9FA border or a very subtle gray background. Use `headline-md` for widget titles. 
- **Checkboxes/Radios:** When selected, they fill with Vibrant Orange. Use the `roundedness: 1` (4px) for checkboxes to ensure they feel intentional but not too circular.
- **Lists:** Clean rows separated by 1px #F8F9FA dividers. Generous vertical padding (16px) between items.