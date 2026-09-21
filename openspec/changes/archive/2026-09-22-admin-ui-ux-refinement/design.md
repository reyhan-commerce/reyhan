# Design Decisions: Modern Enterprise UI/UX Refinement

## Architectural Conventions
- **Grid Layout Strategy**: Use `Grid::make(3)` with `columnSpan(['default' => 3, 'lg' => 2])` for primary content and `columnSpan(['default' => 3, 'lg' => 1])` for sidebar controls.
- **Visual Consolidation**: Use `description(fn ($record) => ...)` on primary text columns (`name`, `order_number`) to render secondary identifiers (slug, SKU, recipient phone, email) without cluttering separate table columns.
- **Empty States**: Use `emptyStateHeading()`, `emptyStateDescription()`, and `emptyStateIcon()` on all Filament `Table` definitions with Persian localization.
- **Filter Presentation**: Configure `filtersFormColumns(2)` on all resources with multiple filters for comfortable touch and desktop interaction.
- **Number Formatting**: All monetary values rendered through `number_format((int) ($state / 10))` with explicit `تومان` suffix and Persian digit readability.
