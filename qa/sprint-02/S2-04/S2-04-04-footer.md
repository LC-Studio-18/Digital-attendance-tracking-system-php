# S2-04-04 - Create Reusable Footer

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Nithaam
- QA status: Partial - responsive rendering passes; small-screen text readability needs review
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; Chromium at 360px and 1440px using an isolated include fixture.

## Objective
- Build a reusable PHP footer include matching the approved design.

## Acceptance Criteria
- [ ] Footer can be included and rendered consistently on applicable pages.
- [ ] Footer content and appearance match the approved design.
- [ ] Footer remains readable and usable at mobile and desktop widths.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | Render the footer on a page at mobile and desktop widths | Footer appears once, remains readable, and matches the design | Footer rendered at 360px and 1440px without horizontal overflow. At 360px, computed fonts were 9px for the mission, 8px for contact lines, and 7px for metadata; readability and Figma match need review. The current login page uses a separate footer. | Partial | Render passes; small-screen readability and application integration remain open. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | frontend retest | `2ae8032` (`origin/team1`) | Partial | Footer rendered at mobile and desktop widths; 7-9px mobile text is flagged for readability review. |

## Linked Bugs
No bug ID assigned. Confirm small-screen footer typography against the approved design.
