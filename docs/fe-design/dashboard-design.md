# Frontend Dashboard Design Specification

## Design System

### Color Palette

```css
:root {
  /* Primary Colors */
  --color-primary: #2563eb;
  --color-primary-dark: #1d4ed8;
  --color-primary-light: #3b82f6;

  /* Secondary Colors */
  --color-secondary: #64748b;
  --color-secondary-dark: #475569;
  --color-secondary-light: #94a3b8;

  /* Semantic Colors */
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-error: #ef4444;
  --color-info: #3b82f6;

  /* Neutral Colors */
  --color-white: #ffffff;
  --color-gray-50: #f8fafc;
  --color-gray-100: #f1f5f9;
  --color-gray-200: #e2e8f0;
  --color-gray-300: #cbd5e1;
  --color-gray-400: #94a3b8;
  --color-gray-500: #64748b;
  --color-gray-600: #475569;
  --color-gray-700: #334155;
  --color-gray-800: #1e293b;
  --color-gray-900: #0f172a;

  /* Background */
  --bg-primary: #ffffff;
  --bg-secondary: #f8fafc;
  --bg-tertiary: #f1f5f9;
}
```

### Typography

```css
:root {
  /* Font Family */
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;

  /* Font Sizes */
  --text-xs: 0.75rem;    /* 12px */
  --text-sm: 0.875rem;   /* 14px */
  --text-base: 1rem;     /* 16px */
  --text-lg: 1.125rem;   /* 18px */
  --text-xl: 1.25rem;    /* 20px */
  --text-2xl: 1.5rem;    /* 24px */
  --text-3xl: 1.875rem;  /* 30px */
  --text-4xl: 2.25rem;   /* 36px */

  /* Font Weights */
  --font-normal: 400;
  --font-medium: 500;
  --font-semibold: 600;
  --font-bold: 700;

  /* Line Heights */
  --leading-tight: 1.25;
  --leading-normal: 1.5;
  --leading-relaxed: 1.75;
}
```

### Spacing

```css
:root {
  --space-1: 0.25rem;   /* 4px */
  --space-2: 0.5rem;    /* 8px */
  --space-3: 0.75rem;   /* 12px */
  --space-4: 1rem;      /* 16px */
  --space-5: 1.25rem;   /* 20px */
  --space-6: 1.5rem;    /* 24px */
  --space-8: 2rem;      /* 32px */
  --space-10: 2.5rem;   /* 40px */
  --space-12: 3rem;     /* 48px */
  --space-16: 4rem;     /* 64px */
}
```

### Border Radius

```css
:root {
  --radius-sm: 0.25rem;
  --radius-md: 0.375rem;
  --radius-lg: 0.5rem;
  --radius-xl: 0.75rem;
  --radius-2xl: 1rem;
  --radius-full: 9999px;
}
```

### Shadows

```css
:root {
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}
```

## Component Specifications

### Button Component

**Variants:**

| Variant | Background | Text | Border | Hover |
|---------|-----------|------|--------|-------|
| Primary | --color-primary | white | none | --color-primary-dark |
| Secondary | white | --color-gray-700 | --color-gray-300 | --color-gray-50 |
| Danger | --color-error | white | none | darker |
| Ghost | transparent | --color-gray-600 | none | --color-gray-100 |

**Sizes:**

| Size | Padding | Font Size | Height |
|------|---------|-----------|--------|
| sm | 8px 12px | 14px | 32px |
| md | 10px 16px | 14px | 40px |
| lg | 12px 24px | 16px | 48px |

**States:**
- Default
- Hover
- Active/Pressed
- Disabled
- Loading

### Input Component

**Structure:**
```
[Label]
[Input Field] [Optional Icon]
[Helper Text / Error Message]
```

**States:**
- Default: --color-gray-300 border
- Focus: --color-primary border, shadow ring
- Error: --color-error border
- Disabled: --color-gray-100 background

### Data Table

**Structure:**
```
+--------------------------------------------------+
| Column Header | Column Header | Column Header    |
+--------------------------------------------------+
| Cell          | Cell          | Cell             |
| Cell          | Cell          | Cell             |
| Cell          | Cell          | Cell             |
+--------------------------------------------------+
| Pagination: < 1 2 3 4 5 >                        |
+--------------------------------------------------+
```

**Features:**
- Sortable columns (indicated by arrow)
- Alternating row colors
- Hover state on rows
- Fixed header on scroll

### Card Component

**Structure:**
```
+----------------------------------+
| [Header]                         |
+----------------------------------+
|                                  |
| [Content]                        |
|                                  |
+----------------------------------+
| [Footer / Actions]               |
+----------------------------------+
```

**Styles:**
- Background: white
- Border: 1px --color-gray-200
- Border Radius: --radius-lg
- Shadow: --shadow-sm

### Modal Component

**Structure:**
```
+----------------------------------+
| Title                        [X] |
+----------------------------------+
|                                  |
| Content Area                     |
|                                  |
+----------------------------------+
| [Cancel]              [Confirm]  |
+----------------------------------+
```

**Overlay:** rgba(0, 0, 0, 0.5)

**Sizes:**
- sm: 400px width
- md: 560px width
- lg: 720px width

### Status Badge

**Variants:**

| Status | Background | Text |
|--------|-----------|------|
| Active | --color-success-light | --color-success |
| Draft | --color-warning-light | --color-warning |
| Archived | --color-gray-200 | --color-gray-600 |
| Syncing | --color-info-light | --color-info |
| Error | --color-error-light | --color-error |

## Page Layouts

### Dashboard Layout

```
+----------------------------------------------------------+
| [Logo]         [Navigation]              [User Menu]      |
+----------------------------------------------------------+
|         |                                                 |
| Sidebar |  Main Content Area                              |
|         |                                                 |
|  [Nav]  |  +-------------------------------------------+  |
|         |  | Page Header                               |  |
|  [Nav]  |  +-------------------------------------------+  |
|         |  |                                           |  |
|  [Nav]  |  | Content                                   |  |
|         |  |                                           |  |
|  [Nav]  |  +-------------------------------------------+  |
|         |                                                 |
+----------------------------------------------------------+
```

**Sidebar:**
- Width: 256px (expanded), 64px (collapsed)
- Background: --color-gray-900
- Text: --color-gray-300
- Active item: --color-primary background

### Products List Page

```
+----------------------------------------------------------+
| Products                                    [Sync Button] |
+----------------------------------------------------------+
| [Search Input]           [Filter] [Sort]                  |
+----------------------------------------------------------+
|                                                           |
| +-------------------------------------------------------+ |
| | SKU      | Title           | Status | Price  | Stock  | |
| |----------|-----------------|--------|--------|--------| |
| | TEST-001 | Product Name    | Active | $19.99 | 100    | |
| | TEST-002 | Another Product | Draft  | $29.99 | 50     | |
| +-------------------------------------------------------+ |
|                                                           |
| < Previous  1  2  3  ...  10  Next >                     |
+----------------------------------------------------------+
```

### Product Detail Page

```
+----------------------------------------------------------+
| < Back to Products                                        |
+----------------------------------------------------------+
| +-------------------+  +--------------------------------+ |
| |                   |  | Title: Product Name            | |
| |   Product Image   |  | SKU: TEST-001                  | |
| |                   |  | Status: [Active]               | |
| +-------------------+  | Price: $19.99                  | |
|                        | Stock: 100 units               | |
|                        +--------------------------------+ |
|                                                           |
| Variants                                                  |
| +-------------------------------------------------------+ |
| | SKU        | Price  | Stock | Actions                 | |
| |------------|--------|-------|-------------------------| |
| | TEST-001-S | $19.99 | 30    | [Edit] [Delete]         | |
| | TEST-001-M | $19.99 | 40    | [Edit] [Delete]         | |
| +-------------------------------------------------------+ |
+----------------------------------------------------------+
```

### Sync Page

```
+----------------------------------------------------------+
| Product Sync                                              |
+----------------------------------------------------------+
|                                                           |
| Sync Status: [Idle]                                       |
|                                                           |
| +-------------------------------------------------------+ |
| | Single Product Sync                                   | |
| | +------------------------------------------+          | |
| | | Enter Shopify Product ID                 |  [Sync]  | |
| | +------------------------------------------+          | |
| +-------------------------------------------------------+ |
|                                                           |
| +-------------------------------------------------------+ |
| | Bulk Sync                                             | |
| | Sync all products from Shopify store                  | |
| | [Start Bulk Sync]                                     | |
| +-------------------------------------------------------+ |
|                                                           |
| Recent Sync History                                       |
| +-------------------------------------------------------+ |
| | Date       | Type   | Products | Status               | |
| |------------|--------|----------|----------------------| |
| | 2024-01-15 | Bulk   | 150      | Completed            | |
| | 2024-01-14 | Single | 1        | Completed            | |
| +-------------------------------------------------------+ |
+----------------------------------------------------------+
```

## Responsive Breakpoints

```css
/* Mobile */
@media (max-width: 640px) { }

/* Tablet */
@media (min-width: 641px) and (max-width: 1024px) { }

/* Desktop */
@media (min-width: 1025px) { }

/* Large Desktop */
@media (min-width: 1280px) { }
```

## Accessibility Guidelines

1. Color contrast ratio minimum 4.5:1 for text
2. Focus indicators visible on all interactive elements
3. All images have alt text
4. Form inputs have associated labels
5. Error messages announced to screen readers
6. Keyboard navigation supported
7. Skip to main content link provided

## Animation Guidelines

```css
/* Transitions */
--transition-fast: 150ms ease;
--transition-normal: 200ms ease;
--transition-slow: 300ms ease;

/* Use for: */
/* - Button hover/active states */
/* - Input focus states */
/* - Modal open/close */
/* - Sidebar collapse/expand */
```

## Loading States

1. **Skeleton Loaders** - For content areas (tables, cards)
2. **Spinner** - For buttons and small actions
3. **Progress Bar** - For long operations (bulk sync)
4. **Overlay** - For full-page loading

## Empty States

Each page should have meaningful empty states:

- Products: "No products yet. Sync your first product from Shopify."
- Sync History: "No sync operations yet. Start by syncing a product."
- Search Results: "No products match your search criteria."
