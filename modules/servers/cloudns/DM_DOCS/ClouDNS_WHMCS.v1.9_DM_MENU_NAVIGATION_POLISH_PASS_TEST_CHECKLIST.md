# Test Checklist

## Desktop menu
- DNS Records remains flush to the left edge and keeps its left-side rounding.
- Mail Forwards, Statistics, Status, and Advanced align evenly.
- Active top-level items use the orange active underline/background.
- Advanced tab stays flat, not rounded like a raised tab.
- Advanced dropdown opens and is not clipped.
- Advanced dropdown order remains:
  - SOA
  - DNSSEC
  - SSL
  - Zone Transfers
  - Import Zone File
  - Export Zone File
  - Deactivate Zone
- Deactivate Zone is visually dangerous/red.

## Mobile menu
- Settings Menu button has the same white/bordered style.
- Active mobile item is highlighted.
- Advanced mobile items show active state on their pages.
- Mobile Deactivate Zone is red/danger styled.
- Dividers and Advanced header are clean and readable.

## Regression checks
- Main navigation links still work.
- Advanced links still work.
- Advanced dropdown hover/click behavior still works.
- DNS Records and Mail Forwards actions still work.
- No DNS/API behavior changed.
