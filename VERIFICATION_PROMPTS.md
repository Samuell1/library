# Performance Bottleneck Verification Prompts

Copy-paste these prompts into your local Claude Code instance to independently verify each bottleneck.

---

## 🔴 Bottleneck #1: Model Event Firing on Every Attribute Access

```
Analyze src/Database/Concerns/HasAttributes.php and find the getAttributeValue() method.

Show me:
1. How many times fireEvent() is called per attribute access
2. What events are fired (beforeGetAttribute, getAttribute)
3. Compare this to Laravel's implementation (no event firing)
4. Calculate the overhead for a model with 20 attributes accessed 100 times
5. Show real code examples demonstrating the performance impact

Provide specific line numbers and code snippets.
```

---

## 🔴 Bottleneck #2: Double Event Loop in attributesToArray()

```
Read src/Database/Concerns/HasAttributes.php and examine the attributesToArray() method.

Analyze:
1. Count how many foreach loops fire events
2. Identify if events are fired twice (beforeGetAttribute + getAttribute)
3. Calculate total event fires for a model with 20 attributes being serialized to JSON
4. Show me if this happens in addition to events already fired in getAttributeValue()
5. Compare to Laravel's attributesToArray() implementation

Show the problematic code with line numbers and explain why it's redundant.
```

---

## 🟠 Bottleneck #3: Reflection-Based Extension Loading

```
Examine src/Extension/ExtendableTrait.php focusing on:

1. extensionExtractMethods() method - find uses of get_class_methods()
2. extendableCallStatic() method - find uses of new ReflectionClass()
3. Count how many reflection operations happen when loading a behavior
4. Show where method maps are stored and if they're cached
5. Estimate overhead for a model with 3 behaviors loaded

Provide the exact code that uses reflection and explain the performance cost.
```

---

## 🟠 Bottleneck #4: Event Sorting on Every Fire

```
Read src/Support/Traits/Emitter.php and analyze the event firing mechanism.

Find:
1. Where event sorting happens (emitterEventSortEvents method)
2. When the emitterEventSorted cache is invalidated
3. Show the krsort() and array_merge operations
4. Explain why sorting happens in fireEvent() instead of bindEvent()
5. Calculate overhead for an event with 50 listeners fired 100 times

Compare to Laravel's event dispatcher that sorts during registration.
```

---

## 🟡 Bottleneck #5: Query Cache Key Generation

```
Examine src/Database/QueryBuilder.php and find the generateCacheKey() method.

Analyze:
1. What hashing algorithm is used (md5)
2. What gets serialized (bindings)
3. Calculate overhead for a query with 10 bindings
4. Research why Laravel removed query caching in version 5.8
5. Suggest modern alternatives (Redis, application-level caching)

Show the code and explain why serialize() + md5() is slow.
```

---

## 🟡 Bottleneck #6: Nested Tree Complex SQL Generation

```
Read src/Database/Traits/NestedTree.php and examine the performMove() method (around line 877-934).

Analyze:
1. Show the CASE statement SQL generation for left/right columns
2. Count how many BETWEEN clauses are used
3. Explain the boundary calculation algorithm (getSortedBoundaries)
4. Estimate query complexity for a tree with 1000 nodes
5. Compare nested sets vs closure tables vs adjacency lists

Provide the actual SQL generated and explain the performance trade-offs.
```

---

## 🟡 Bottleneck #7: Asset Combiner MD5 Hash per File

```
Examine src/Assetic/Combiner.php focusing on hash generation.

Find:
1. Where md5() is called for file paths
2. Where file content is hashed
3. Count total hash operations for 50 CSS files
4. Show if hashing is done sequentially or could be batched
5. Compare md5() vs modern hash functions (xxh3, xxh64)

Show the code and calculate overhead for a typical theme with 50 assets.
```

---

## 🔵 Bottleneck #8: Extension Method Lookup

```
In src/Extension/ExtendableTrait.php, find the __call() method.

Analyze:
1. How extension methods are looked up (array iteration?)
2. What data structure stores the method map
3. If there's any caching mechanism
4. Estimate overhead for 50 extension methods
5. Suggest optimization (hash map vs array iteration)

Show the lookup code and explain the complexity (O(n) vs O(1)).
```

---

## 🔵 Bottleneck #9: Relation Type Lookup

```
Read src/Database/Concerns/HasRelationships.php and find how relations are resolved.

Examine:
1. The getRelationDefinition() or similar method
2. How $hasMany, $belongsTo arrays are iterated
3. Compare property-based relations vs Laravel's method-based approach
4. Determine if this is actually faster or slower than Laravel
5. Show benchmarks if available

Explain whether this is a real bottleneck or already optimized.
```

---

## 🔵 Bottleneck #10: Router Linear Search

```
Analyze src/Router/Router.php, specifically the match() method.

Find:
1. The foreach loop over $this->routeMap
2. How route matching is done (linear search?)
3. If routes are sorted or compiled
4. Calculate overhead for 100 routes vs 1000 routes
5. Compare to Laravel's compiled route caching

Show the matching algorithm and suggest route compilation strategy.
```

---

## 🔵 Bottleneck #11: Class Hierarchy Walking

```
In src/Extension/ExtendableTrait.php, examine the extendableConstruct() method.

Analyze:
1. How class hierarchy is walked (get_parent_class loop?)
2. What initialization callbacks are collected
3. If this happens once per class or per instance
4. Estimate overhead for 5-level deep inheritance
5. Determine if caching would help

Show the hierarchy walking code and explain the cost.
```

---

## 🔵 Bottleneck #12: debug_backtrace() Usage

```
Search src/Database/Concerns/HasRelationships.php for debug_backtrace() calls.

Find:
1. Where and why debug_backtrace() is called
2. How many stack frames are analyzed
3. If this can be avoided with better design
4. Compare to Laravel's Relation::noConstraints() approach
5. Determine if the developer experience trade-off is worth it

Show the code and explain why this trade-off might be acceptable.
```

---

## 🎯 Comprehensive Analysis Prompt

```
Perform a comprehensive performance analysis of the October CMS library compared to Laravel:

1. Read and analyze all files in src/Database/Concerns/
2. Examine src/Extension/ExtendableTrait.php
3. Review src/Support/Traits/Emitter.php
4. Study src/Router/Router.php
5. Analyze src/Database/QueryBuilder.php

For each file, identify:
- Use of reflection (get_class_methods, ReflectionClass)
- Event firing in hot paths (loops, attribute access)
- Sorting/array operations in frequently-called methods
- Hashing operations (md5, serialize)
- Linear searches through collections

Create a ranked table of bottlenecks with:
- Location (file:line)
- Severity (CRITICAL/HIGH/MEDIUM/LOW)
- Impact (milliseconds per operation)
- Comparison to Laravel equivalent
- Optimization suggestions

Provide specific code examples and performance calculations.
```

---

## 🔬 Benchmark Creation Prompt

```
Create PHPBench benchmarks to measure the performance bottlenecks identified:

1. Create benchmarks/ModelAttributeBench.php to test:
   - Attribute access with/without events
   - attributesToArray() with 20 attributes
   - JSON serialization with nested relations

2. Create benchmarks/ExtensionBench.php to test:
   - Model instantiation with 0, 1, 3, 5 behaviors
   - Extension method calls
   - Reflection overhead

3. Create benchmarks/EventBench.php to test:
   - Event firing with 10, 50, 100 listeners
   - Event sorting overhead
   - bindEvent() + fireEvent() cycle

4. Create benchmarks/QueryBench.php to test:
   - generateCacheKey() with various binding sizes
   - Query building without cache key generation

5. Create benchmarks/RouterBench.php to test:
   - Route matching with 10, 100, 1000 routes
   - URL generation

Use the existing phpbench.json configuration and follow October's benchmarking conventions.
```

---

## 🔍 Validation Prompt (Quick Check)

```
Quick verification of top 4 bottlenecks:

1. Check src/Database/Concerns/HasAttributes.php lines 130 and 158
   - Confirm fireEvent() is called twice in getAttributeValue()

2. Check src/Database/Concerns/HasAttributes.php lines 24 and 54
   - Confirm attributesToArray() has two foreach loops with fireEvent()

3. Check src/Extension/ExtendableTrait.php lines 138 and 508-534
   - Confirm get_class_methods() and new ReflectionClass() usage

4. Check src/Support/Traits/Emitter.php lines 92-93
   - Confirm sorting happens in fireEvent() not bindEvent()

Provide yes/no confirmation for each with exact line numbers.
```

---

## 📊 Comparison Prompt

```
Create a side-by-side comparison between October CMS and Laravel for these operations:

1. Model attribute access:
   - Show October's code path (with events)
   - Show Laravel's code path (direct access)
   - Estimate performance difference

2. JSON serialization:
   - Show October's attributesToArray()
   - Show Laravel's attributesToArray()
   - Count event fires and operations

3. Extension/Behavior loading:
   - Show October's reflection-based approach
   - Show Laravel's trait-based approach
   - Measure instantiation cost

4. Event system:
   - Show October's Emitter sort-on-fire
   - Show Laravel's Dispatcher sort-on-bind
   - Compare complexity

For each, provide actual code snippets and performance estimates.
```

---

## 💡 Optimization Proposal Prompt

```
For each of the top 4 bottlenecks, create concrete optimization proposals:

1. Model Event Firing:
   - Propose lazy event checking (only if listeners exist)
   - Suggest configuration flag to disable attribute events
   - Show modified code example

2. attributesToArray() Double Loop:
   - Propose removing redundant event firing
   - Show refactored code
   - Calculate performance improvement

3. Reflection-Based Extensions:
   - Propose behavior metadata caching system
   - Design cache structure (similar to Laravel route cache)
   - Show implementation approach

4. Event Sorting:
   - Propose moving sort to bindEvent()
   - Consider SplPriorityQueue
   - Show refactored code

Provide working code examples, not just descriptions.
```

---

## 🧪 Profiling Prompt

```
Create a profiling script that measures actual performance:

1. Setup test data:
   - Create 100 test models with 3 behaviors each
   - Prepare 50 routes
   - Setup event listeners

2. Profile these operations:
   - Model instantiation (measure extension loading)
   - Attribute access (measure event overhead)
   - JSON serialization (measure attributesToArray)
   - Route matching (measure linear search)
   - Event firing (measure sorting)

3. Use Xdebug or Blackfire profiling
4. Generate flame graphs
5. Identify hotspots

Output results in markdown table with:
- Operation
- Time (ms)
- Memory (MB)
- Calls count
- Percent of total time
```

---

## Usage Instructions

1. **Copy any prompt above**
2. **Paste into Claude Code CLI** (local instance)
3. **Review the analysis** it provides
4. **Compare with the PERFORMANCE_BOTTLENECKS_ANALYSIS.md** report
5. **Verify findings independently**

## Batch Verification

To verify all bottlenecks at once, use the **Comprehensive Analysis Prompt** or run them sequentially starting with #1-4 (highest priority).

## Expected Output

Each prompt should provide:
- ✅ Confirmation the bottleneck exists
- 📍 Exact file locations and line numbers
- 💻 Code snippets showing the issue
- 📊 Performance impact estimates
- 🔧 Optimization suggestions

If Claude Code identifies different findings, compare them with this analysis and investigate discrepancies.
