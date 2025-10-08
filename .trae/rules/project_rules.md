# BlackCheetah Project Rules

## Overview
BlackCheetah is a Vue 3 + Vuetify + Laravel admin template with a sophisticated design system. This document outlines the rules and conventions for maintaining consistency across the application.

## Technology Stack
- **Frontend**: Vue 3 with TypeScript
- **UI Framework**: Vuetify 3
- **Backend**: Laravel
- **Build Tool**: Vite
- **State Management**: Pinia
- **Routing**: Vue Router with auto-imports
- **Styling**: SCSS with custom design system

## Project Structure

### Directory Organization
```
resources/ts/
├── @core/           # Core framework components and utilities
├── @layouts/        # Layout components and enums
├── components/      # Reusable UI components
├── pages/           # Application pages (auto-routed)
├── composables/     # Vue composables
├── utils/           # Utility functions
├── plugins/         # Vue plugins
└── navigation/      # Navigation configuration
```

## UI Framework Guidelines

### 1. Component Creation Rules

#### When creating new components:
- **ALWAYS** use existing Vuetify components as base
- **FOLLOW** the established component patterns in `resources/ts/components/`
- **USE** TypeScript with proper type definitions
- **IMPLEMENT** proper props interface with `defineProps<Props>()`
- **EMIT** events using `defineEmits<Emit>()`

#### Component Structure Template:
```vue
<script setup lang="ts">
interface Props {
  // Define props with proper types
}

interface Emit {
  // Define emitted events
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()
</script>

<template>
  <!-- Use Vuetify components with consistent styling -->
</template>
```

### 2. Page Creation Rules

#### When creating new pages:
- **PLACE** pages in `resources/ts/pages/` directory
- **USE** Vue Router auto-import conventions
- **FOLLOW** existing page structure patterns
- **IMPLEMENT** proper meta layouts using `MetaLayouts`
- **USE** consistent grid system with `VRow` and `VCol`

#### Page Structure Template:
```vue
<script setup lang="ts">
// Import required components and composables
</script>

<template>
  <VRow>
    <VCol cols="12">
      <!-- Page content using Vuetify components -->
    </VCol>
  </VRow>
</template>
```

### 3. Dialog/Modal Guidelines

#### For dialogs and modals:
- **USE** existing dialog patterns from `resources/ts/components/dialogs/`
- **IMPLEMENT** proper v-model binding with `isDialogVisible`
- **FOLLOW** the ConfirmDialog pattern for confirmation dialogs
- **USE** consistent dialog sizing: `max-width="500"` for small dialogs
- **APPLY** proper padding: `class="text-center px-10 py-6"`

#### Dialog Template:
```vue
<VDialog
  max-width="500"
  :model-value="props.isDialogVisible"
  @update:model-value="updateModelValue"
>
  <VCard class="text-center px-10 py-6">
    <VCardText>
      <!-- Dialog content -->
    </VCardText>
    <VCardText class="d-flex align-center justify-center gap-2">
      <!-- Action buttons -->
    </VCardText>
  </VCard>
</VDialog>
```

## Design System

### 1. Typography
- **PRIMARY FONT**: "Public Sans" (defined in `$font-family-custom`)
- **FONT SIZES**: Use predefined sizes from `$font-sizes` map
- **HEADINGS**: Follow Vuetify typography scale (h1-h6)
- **BODY TEXT**: Use `body-1` (0.9375rem) and `body-2` (0.8125rem)

### 2. Colors and Theming
- **USE** CSS custom properties: `rgb(var(--v-theme-primary))`
- **FOLLOW** the theme configuration in `themeConfig.ts`
- **SUPPORT** both light and dark themes
- **USE** semantic color names: `primary`, `secondary`, `success`, `warning`, `error`

### 3. Spacing and Layout
- **BORDER RADIUS**: Default 6px (`$border-radius-root`)
- **CARD PADDING**: 24px (`$card-text-padding`)
- **COMPONENT SPACING**: Use Vuetify spacing classes (`ma-`, `pa-`, `mx-`, etc.)
- **GRID SYSTEM**: Use Vuetify's 12-column grid with `VRow` and `VCol`

### 4. Shadows and Elevation
- **CARD ELEVATION**: Default 6 (`$card-elevation`)
- **DIALOG ELEVATION**: 8 (`$dialog-elevation`)
- **MENU ELEVATION**: 8 (`$menu-elevation`)
- **USE** custom shadow system defined in SCSS variables

## Component Conventions

### 1. Buttons
- **DEFAULT HEIGHT**: 38px
- **VARIANTS**: `elevated`, `outlined`, `text`, `tonal`, `flat`, `plain`
- **COLORS**: Use semantic color names
- **ICONS**: Use Tabler icons with proper sizing

### 2. Cards
- **STRUCTURE**: Use `VCard` with `VCardText`, `VCardTitle`, `VCardActions`
- **PADDING**: Follow `$card-text-padding` (24px)
- **ELEVATION**: Use default elevation (6)

### 3. Forms
- **DENSITY**: Use `default`, `comfortable`, or `compact`
- **VALIDATION**: Implement proper form validation patterns
- **LABELS**: Use consistent label styling
- **INPUTS**: Follow Vuetify field conventions

### 4. Navigation
- **VERTICAL NAV**: Use existing vertical navigation patterns
- **HORIZONTAL NAV**: Follow horizontal navigation conventions
- **ICONS**: Use Tabler icons consistently
- **SPACING**: Follow `$vertical-nav-horizontal-spacing`

## Code Style Guidelines

### 1. TypeScript
- **ALWAYS** use TypeScript for all new components
- **DEFINE** proper interfaces for props and emits
- **USE** type imports when necessary
- **IMPLEMENT** proper type checking

### 2. Vue 3 Composition API
- **USE** `<script setup>` syntax
- **IMPLEMENT** proper reactivity with `ref`, `reactive`, `computed`
- **USE** composables for reusable logic
- **FOLLOW** Vue 3 best practices

### 3. Styling
- **USE** Vuetify utility classes when possible
- **IMPLEMENT** custom styles in SCSS files
- **FOLLOW** the existing SCSS structure
- **USE** CSS custom properties for theming

### 4. Auto-imports
- **LEVERAGE** auto-imports for Vue, Vuetify, and utilities
- **AVOID** manual imports for auto-imported items
- **USE** the configured auto-import directories

## File Naming Conventions

### 1. Components
- **PASCAL CASE**: `MyComponent.vue`
- **DESCRIPTIVE**: Use clear, descriptive names
- **CATEGORIZE**: Group related components in subdirectories

### 2. Pages
- **KEBAB CASE**: `my-page.vue`
- **ROUTE BASED**: Follow Vue Router conventions
- **NESTED**: Use directory structure for nested routes

### 3. Composables
- **CAMEL CASE**: `useMyComposable.ts`
- **PREFIX**: Start with `use`
- **DESCRIPTIVE**: Clear function names

## Performance Guidelines

### 1. Component Optimization
- **USE** `defineAsyncComponent` for large components
- **IMPLEMENT** proper lazy loading
- **OPTIMIZE** bundle size with code splitting

### 2. Image Handling
- **USE** SVG for icons and simple graphics
- **OPTIMIZE** images for web
- **IMPLEMENT** lazy loading for images

### 3. State Management
- **USE** Pinia for global state
- **KEEP** component state local when possible
- **IMPLEMENT** proper state persistence

## Accessibility Guidelines

### 1. Semantic HTML
- **USE** proper HTML semantics
- **IMPLEMENT** ARIA attributes when necessary
- **ENSURE** keyboard navigation support

### 2. Color Contrast
- **MAINTAIN** proper color contrast ratios
- **SUPPORT** high contrast mode
- **TEST** with accessibility tools

## Testing Guidelines

### 1. Component Testing
- **WRITE** unit tests for components
- **TEST** user interactions
- **MOCK** external dependencies

### 2. Integration Testing
- **TEST** component integration
- **VERIFY** routing functionality
- **CHECK** state management

## Internationalization (i18n)

### 1. Text Content
- **USE** i18n for all user-facing text
- **SUPPORT** RTL languages (Arabic configured)
- **IMPLEMENT** proper locale switching

### 2. Configuration
- **DEFAULT LOCALE**: English (`en`)
- **SUPPORTED LOCALES**: English, Arabic
- **FILES**: Use JSON format in `lang/` directory

## Build and Deployment

### 1. Development
- **COMMAND**: `npm run dev`
- **HOT RELOAD**: Enabled via Vite
- **TYPE CHECKING**: `npm run typecheck`
- **WEBSITE URL**: `https://blackcheetah.test` (always use this URL for IDE preview testing)

### 2. Production
- **BUILD**: `npm run build`
- **OPTIMIZATION**: Automatic via Vite
- **ASSETS**: Properly hashed and optimized

## Security Guidelines

### 1. Data Handling
- **SANITIZE** user inputs
- **VALIDATE** on both client and server
- **PROTECT** against XSS and CSRF

### 2. Authentication
- **USE** Laravel authentication
- **IMPLEMENT** proper session management
- **SECURE** API endpoints

## Maintenance Guidelines

### 1. Dependencies
- **KEEP** dependencies updated
- **REVIEW** security advisories
- **TEST** after updates

### 2. Code Quality
- **RUN** linting: `npm run lint`
- **MAINTAIN** consistent code style
- **REVIEW** code before merging

## Examples and References

### 1. Existing Components
- **DIALOGS**: `resources/ts/components/dialogs/`
- **FORMS**: `resources/ts/pages/forms/`
- **LAYOUTS**: `resources/ts/@layouts/`

### 2. Documentation
- **VUETIFY**: [Vuetify Documentation](https://vuetifyjs.com/)
- **VUE 3**: [Vue 3 Documentation](https://vuejs.org/)
- **LARAVEL**: [Laravel Documentation](https://laravel.com/docs)

---

**Remember**: Always follow these guidelines when creating new components, pages, or features to maintain consistency and quality across the BlackCheetah application.
