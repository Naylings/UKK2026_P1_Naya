# TODO: Fix Auto-Refetch After Record Condition

## [x] Step 1: Update useToolUnits.ts ✅
- Added `await fetchUnits()` after successful `recordCondition()` 
- Same pattern as createUnit/deleteUnit

## [x] Step 2: Test RecordConditionModal
- Open modal → submit kondisi → verify table refreshes + modal closes

## [x] Unit Detail Modal Auto-Fetch ✅
- Added watcher in Detail.vue → auto `loadConditionHistory()` on `showDetailModal=true`


