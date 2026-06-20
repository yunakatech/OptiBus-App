# UI/UX & Tailwind CSS Development Rules for Vibe Coding

You are an expert Frontend Engineer. Your primary goal is to generate clean, highly proportional, and compact Tailwind CSS user interfaces. You must strictly avoid bloated layouts, oversized typography, and stretched components.

## 1. Layout & Sizing Constraints (No Bloated UI)
- **Never stretch components:** Do not use `w-full` on layout containers without a maximum width wrapper (`max-w-*`).
- **Standard Widths:**
  - Small Cards / Popovers / Modals: `max-w-sm` (384px) or `max-w-md` (448px).
  - Forms / Settings Panels: `max-w-xl` (576px) or `max-w-2xl` (672px).
  - Main Dashboards / Tables: `max-w-6xl` (1152px) or `max-w-7xl` (1280px).
- **Component Heights:** 
  - Standard buttons and input fields MUST use `h-9` (compact) or `h-10` (default). Never use arbitrary heights or let them expand vertically.

## 2. Typography Hierarchy (Strict Scaling)
AI-generated text is often too large. Enforce this scale for SaaS/Web Applications:
- **Main Headings (Page Title):** `text-lg md:text-xl font-bold tracking-tight` (Max `text-2xl` for landing pages).
- **Sub-headings (Section Title):** `text-base font-semibold`.
- **Body Text / Labels:** `text-sm font-medium` or `text-sm text-muted-foreground`.
- **Small Print / Captions:** `text-xs text-muted-foreground`.

## 3. Spacing & Density Rules (Data-Dense Design)
- **Padding:** Use `p-4` or `p-5` for standard cards. Use `p-6` ONLY for large dashboard sections. Never use `p-8` or above.
- **Gaps:** Use `gap-2` or `gap-3` for tight layouts (flex/grid). Use `gap-4` for standard spacing. Never use `gap-8`.
- **Margins:** Keep vertical flow tight with `space-y-3` or `space-y-4`.

## 4. Design System Tokens (Shadcn/Modern Web Inspired)
Unless specified otherwise, use these modern aesthetic classes:
- **Borders & Corners:** Use `rounded-lg` or `rounded-md`. Use subtle borders: `border border-zinc-200` (light mode) or `border-zinc-800` (dark mode).
- **Backgrounds:** Use `bg-white` or `bg-zinc-50/50` for cards.
- **Interactions:** Always include transition states: `transition-colors hover:bg-zinc-50 active:scale-[0.98]`.

## 5. Responsive Behavior
- All components must be designed **Mobile-First**.
- Use responsive prefixes properly: e.g., `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`.
- Do not let grids stretch infinitely on ultrawide monitors.

## 6. Code Output Requirements
- Return only clean, semantic HTML/JSX/Vue templates.
- Group Tailwind classes logically: Layout (`flex`, `grid`) -> Sizing (`w-`, `h-`) -> Spacing (`p-`, `m-`) -> Typography (`text-`) -> Appearance (`bg-`, `border`, `rounded`).
- Minimize code comments; keep classes readable.

## 7. Responsive Table Rules (Anti-Overflow)
- **Always Wrap Tables:** Every `<table>` element MUST be wrapped inside a responsive container: `<div class="w-full overflow-x-auto border border-zinc-200 rounded-lg">`.
- **Prevent Text Wrapping / Clipping:** Use `whitespace-nowrap` on text inside `<th>` and `<td>` to keep rows clean, but combine it with adequate padding (`px-4 py-3`).
- **Data Density:** Tables must be compact. Use `text-xs` or `text-sm` for data. Table rows should use vertical padding of `py-2` or `py-2.5` maximum.
- **Fixed Width Strategy:** For critical columns (e.g., ID, Status, Actions), assign fixed widths like `w-20` or `w-24`. Let the main content column (e.g., Name, Description) take the remaining space using `w-full` or `min-w-[200px]`.
- **Card-Flip Trick for Mobile (Optional/Advanced):** If a table has too many columns, suggest a responsive layout that changes from a standard table on desktop (`md:table`) to a stacked card list on mobile (`block md:hidden`).
