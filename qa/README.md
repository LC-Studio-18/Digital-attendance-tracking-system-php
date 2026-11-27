# Team 1 - Quality Assurance

## Project
Digital Attendance Tracking System

## Team Information
- Team: 1
- QA tester: Ahlumile Totose
- QA branch: `QA1`
- Current sprint: Sprint 2

## Responsibilities
- Verify assigned work against its acceptance criteria and Definition of Done.
- Record test cases, results, defects, and retest outcomes.
- Keep QA records on `QA1`; do not merge developer code into the QA branch for testing.
- Record the exact commit, build, or Figma version tested.

## Documentation Structure
- `templates/` - Reusable ticket, subtask, and bug report templates.
- `sprint-02/` - Sprint 2 testing records, organized by ticket.

For each assigned ticket, create `sprint-02/<TICKET-ID>/README.md`. Add subtask reports and a `bugs/` folder there when needed. Use the templates in `templates/`.

## Testing Workflow
1. Confirm the ticket, acceptance criteria, and build or design version to test.
2. Test developer code in an isolated worktree or environment; keep `QA1` separate.
3. Record results and evidence in the relevant Markdown report.
4. Report defects to the responsible developer and link them to the affected test.
5. Retest fixes and run relevant regression checks.
6. Commit QA documentation to `QA1`.

## Test Statuses
- Pass: Expected result achieved.
- Fail: Actual result differs from the expected result.
- Blocked: A dependency prevents the test from being completed.
- Not Run: The test has not been executed.

## Resources
- [Project design in Figma](https://www.figma.com/design/Tlkxebvj6tGK2rqH57wFXN/S1-08?node-id=0-1&t=0aqGnI5ErYzrNuwV-1) - reference only; record the exact version in a ticket report before testing.
