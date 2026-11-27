# S2-04-03 - Create Navigation with Mobile Menu

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Nithaam
- QA status: Partial - mobile controls work; role mapping and destination integration remain unresolved
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; Chromium at 360px and 1440px using an isolated include fixture.

## Objective
- Implement reusable site navigation, including a mobile menu.

## Acceptance Criteria
- [ ] Navigation links lead to their intended destinations.
- [ ] Mobile menu can be opened and closed using its visible control.
- [ ] Navigation matches the approved design and is usable at mobile and desktop widths.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Use navigation links and toggle the menu on mobile | Links navigate correctly and the menu opens and closes as expected | Admin desktop links and mobile bottom navigation rendered. The mobile profile menu opened with `aria-expanded=true`; Escape closed it and returned focus to the toggle. Navigation has no application call site or destination pages in the current frontend. It renders for `admin`/`employee`, but returns no markup for backend role `staff`. | Partial | Interaction passed in isolated fixture; role mapping and real link destinations require integration. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | frontend retest | `2ae8032` (`origin/team1`) | Partial | Mobile dropdown open/close and desktop/mobile navigation rendering passed; staff role and destination integration remain unresolved. |

## Linked Bugs
No bug ID assigned. Navigation accepts `admin` and `employee`, while backend roles are `admin` and `staff`; confirm or map the staff role before wiring this component into authenticated pages.
