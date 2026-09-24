#!/usr/bin/env bash

# -----------------------------------------------------------------------------
# Farshid Laravel Skill — Single Prompt Bundle Generator
# Combines all reference files, examples, and SKILL.md into a single document
# for web-based AI tools (Claude Projects, Custom GPTs, etc.)
# -----------------------------------------------------------------------------

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SKILL_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
DIST_DIR="${SKILL_ROOT}/dist"
OUTPUT_FILE="${DIST_DIR}/farshid-laravel-single-prompt.md"

mkdir -p "${DIST_DIR}"

echo "Generating single-file prompt bundle at: ${OUTPUT_FILE}..."

cat << 'HEADER' > "${OUTPUT_FILE}"
# Farshid's Laravel AI Coding Master Instructions (Complete Edition)
This document contains Farshid's complete Laravel AI Coding Standards, Architecture Constitution,
decision trees, and reference code. Apply these rules to all Laravel/PHP tasks.

HEADER

echo "Appending SKILL.md..."
echo -e "\n\n---\n" >> "${OUTPUT_FILE}"
cat "${SKILL_ROOT}/SKILL.md" >> "${OUTPUT_FILE}"

echo "Appending References..."
for ref in "${SKILL_ROOT}/references"/*.md; do
    echo -e "\n\n---\n" >> "${OUTPUT_FILE}"
    echo "## Reference: $(basename "$ref")" >> "${OUTPUT_FILE}"
    cat "$ref" >> "${OUTPUT_FILE}"
done

echo "Appending Examples..."
for ex in "${SKILL_ROOT}/examples"/*.php; do
    echo -e "\n\n---\n" >> "${OUTPUT_FILE}"
    echo "## Example: $(basename "$ex")" >> "${OUTPUT_FILE}"
    echo '```php' >> "${OUTPUT_FILE}"
    cat "$ex" >> "${OUTPUT_FILE}"
    echo '```' >> "${OUTPUT_FILE}"
done

echo "✅ Generated successfully: ${OUTPUT_FILE}"
