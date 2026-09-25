# Test Checklist

## Under Limit
- Use a product where zones used is less than the package limit.
- Confirm the counter shows something like `Zones: 3/10`.
- Confirm the `+Add` button is visible.
- Confirm `+Add` opens Add New DNS Zone.

## At Limit
- Use a product where zones used equals the package limit.
- Confirm the counter shows something like `Zones: 10/10`.
- Confirm `+Add` is replaced by `Limit Reached`.
- Confirm `Limit Reached` is gray/disabled-looking.
- Confirm `Limit Reached` is not clickable.

## Regression
- Zones List still loads.
- Manage/Delete still work.
- Actions alignment remains correct.
- No DNS/API behavior changed.
