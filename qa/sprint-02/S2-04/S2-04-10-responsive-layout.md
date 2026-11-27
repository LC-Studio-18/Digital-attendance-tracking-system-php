# S2-04-10 - Verify Responsive Layouts

## Information
- Parent ticket: S2-04 - Shared Layout and Login Page
- Assigned developer: Nithaam (shared layout), Anela (login page)
- QA status: Partial - no overflow at target widths; footer text readability needs review
- Build / commit SHA: `2ae8032` (`origin/team1`)
- Figma design / version tested: [Open Figma design](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - version:
- Test environment: Windows/XAMPP; PHP 8.0.30; Chromium at 360px, 390px, and 1440px.

## Objective
- Ensure the shared header, navigation, footer, and login page work on mobile and desktop.

## Acceptance Criteria
- [ ] Shared layout and login page are checked at 360px viewport width.
- [ ] Shared layout and login page are checked at desktop width.
- [ ] No unintended horizontal overflow or inaccessible controls are present.

## Test Cases

| Test ID | Scenario | Expected result | Actual result | Status | Evidence / bug |
|---|---|---|---|---|---|
| TC-01 | View all assigned frontend parts at 360px and desktop width | Content adapts without unintended overflow and controls remain usable | Login controls fit and the page had no horizontal overflow at 360px, 390px, or 1440px. The shared navigation/footer fixture also had no horizontal overflow at 360px and 1440px. Footer text at 360px computed to 7-9px and needs readability review. | Partial | Responsive geometry passed; small-screen typography and Figma comparison remain open. |

## Test History

| Date | Tester | Test / retest / regression | Version tested | Result | Notes |
|---|---|---|---|---|---|
| 2026-11-27 | QA | responsive retest | `2ae8032` (`origin/team1`) | Partial | Tested login and shared components at 360px, 390px, and 1440px in Chromium. |

## Linked Bugs
No bug ID assigned. Review 7-9px footer text at 360px against the approved design.
