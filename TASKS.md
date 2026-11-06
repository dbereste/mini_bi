# Take-home assignment: Mini BI

**Time box: 2-3 hours.** We don't expect you to fix everything. We care more about how you
prioritise, investigate and explain than about how many items you get through. Stop when the
time is up and write down what you would do next.

## Context

You've just joined the BI team. The Marketing team uses the **Affiliate Performance Overview**
page every morning, and they have three complaints:

> "It takes forever to load."
> "The table doesn't add up to the Total Deposits card, so which number is right?"
> "It's unusable on my phone."

You own this page now.

## 1. Performance (must)

- With the default filters, the page takes far too long. Make it fast. As a guide, aim for
  under 1 second of server time, including when someone selects a whole year.
- Show how you measured: number of queries, timings, `EXPLAIN QUERY PLAN`, or anything else you used.
- Make all schema changes as Laravel migrations.

## 2. Data correctness (must)

- Find out why the numbers don't agree. Every number on the page should be correct. Where two
  numbers legitimately differ, explain why in your notes, and consider whether the UI should make that clear.
- Some metric definitions are ambiguous. When you have to choose one, write down your assumption.

## 3. Mobile (must)

- Make the page usable on a phone that is 375px wide: the cards, the filters and the table.

## 4. Code review (should)

- Review the `Overview` module as if it were a pull request from a colleague.
- Fix what you consider risky, and list everything else in your notes, ranked by severity.

## 5. Bonus (only if you have time)

- The CSV export.
- The nightly `bi:sync-daily-activity` command.
- Automated tests for what you changed.

## What to send back

1. The repository (a zip or a git bundle) with **small, focused commits**, one concern per commit.
2. A `NOTES.md` that covers:
   - each problem you found: what it was, how you found it, how you fixed it, and its impact before and after;
   - the assumptions you made;
   - what you would do next with more time.

In the follow-up interview, we will run your migrations against a fresh copy of the
database and ask you to walk us through your changes.
