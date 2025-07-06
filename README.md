# 📘 Easy Supabase PHP Class – Beginner Guide & Full Documentation

This class allows you to communicate with your **Supabase** project directly from PHP, without Composer or external libraries. Use it to manage posts, users, comments, or anything stored in Supabase.

---

## 🔧 Setup

```php
require_once 'Supabase.php';

$url = 'https://your-project.supabase.co'; // your Supabase URL
$key = 'your-api-key'; // anon or service role

$db = new Supabase($url, $key);
```

---

## 🔨 All Available Methods

### `select($table, $columns = '*', $filters = [], $extras = [])`

Get rows from a table, with filtering and sorting.

```php
$posts = $db->select('blog', '*', [
  'is_published' => ['eq' => true]
], [
  'order' => 'created_at.desc',
  'limit' => 5
]);
```

---

### `insert($table, $data)`

Insert one or more rows.

```php
// Single row
$db->insert('blog', [
  'title' => 'My Post',
  'slug' => 'my-post'
]);

// Multiple rows
$db->insert('blog', [
  ['title' => 'Post A', 'slug' => 'a'],
  ['title' => 'Post B', 'slug' => 'b']
]);
```

---

### `bulkInsert($table, $rows, $chunkSize = 500)`

Insert large datasets in chunks of 500 rows.

```php
$db->bulkInsert('comments', $largeArrayOfRows);
```

---

### `update($table, $match, $data)`

Update a row based on conditions.

```php
$db->update('blog', ['slug' => 'my-post'], [
  'title' => 'Updated Title'
]);
```

---

### `delete($table, $match)`

Delete rows by condition.

```php
$db->delete('blog', ['slug' => 'old-post']);
```

---

### `exists($table, $column, $value)`

Check if a row exists.

```php
if ($db->exists('blog', 'slug', 'hello-world')) {
  echo "Slug exists!";
}
```

---

### `count($table, $filters = [])`

Count how many rows match a condition.

```php
$total = $db->count('blog', ['is_published' => ['eq' => true]]);
```

---

### `findOne($table, $filters = [], $columns = '*')`

Get the first matching row.

```php
$post = $db->findOne('blog', ['slug' => ['eq' => 'about-us']]);
```

---

### `findOrCreate($table, $filters, $data = null)`

Find a row or insert it if missing.

```php
$cat = $db->findOrCreate('categories', ['slug' => 'php'], [
  'name' => 'PHP',
  'slug' => 'php'
]);
```

---

### `updateOrCreate($table, $matchConditions, $data)`

Update a row if it exists, or insert it if not.

```php
$db->updateOrCreate('blog', ['slug' => 'intro'], [
  'title' => 'Intro to Supabase',
  'content' => '...'
]);
```

---

## 🔍 Filter Operators

| Operator | Meaning             | Example                            |
|----------|---------------------|------------------------------------|
| `eq`     | Equals              | `'slug' => ['eq' => 'value']`     |
| `neq`    | Not equals          | `'id' => ['neq' => 1]`            |
| `gt`     | Greater than        | `'views' => ['gt' => 100]`        |
| `lt`     | Less than           | `'views' => ['lt' => 50]`         |
| `like`   | Pattern match       | `'title' => ['like' => 'Hello%']` |
| `ilike`  | Case-insensitive    | `'title' => ['ilike' => '%php%']` |
| `in`     | Match a list        | `'id' => ['in' => [1, 2, 3]]`     |

---

## 💡 Common Use Cases

### Create a blog post
```php
$db->insert('blog', [
  'title' => 'Hello World',
  'slug' => 'hello-world',
  'is_published' => true
]);
```

### Check if a slug exists
```php
if (!$db->exists('blog', 'slug', 'hello-world')) {
  $db->insert('blog', [...]);
}
```

### Get published posts
```php
$db->select('blog', '*', ['is_published' => ['eq' => true]]);
```

### Update a post by slug
```php
$db->update('blog', ['slug' => 'hello-world'], ['title' => 'New Title']);
```

---

## 🧠 Beginner Tips

- Always wrap filter values: `['eq' => 'value']`
- Use `bulkInsert()` instead of calling `insert()` in loops
- Use `exists()` to check slugs, usernames, or other unique values
- `findOrCreate()` and `updateOrCreate()` are safe shortcuts
- Use `order` and `limit` to paginate results

---

MIT License • Use and adapt freely ✨
