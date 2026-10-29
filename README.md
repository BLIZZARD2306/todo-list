# My First PHP To-Do List

A small project for learning how PHP forms and a MySQL database work together.

You can add tasks, see your list, mark tasks complete, and delete tasks. Focus on understanding those four actions first.

## What each technology does

| Technology | Its job in this project |
| --- | --- |
| HTML | Displays the form, task list, and buttons. |
| CSS | Adds colors, spacing, and a simple layout. |
| PHP | Receives forms and tells the database what to do. |
| MySQL | Stores tasks so they remain after you close the browser. |
| PDO | A built-in PHP interface used to connect to and query MySQL. |

This is plain PHP, with no framework. You do not need JavaScript, Node.js, or `npm run dev` to run it.

## The four files to focus on

```text
todo-list/
├── src/
│   ├── config.php             # Database connection
│   ├── schema.sql             # Database table
│   └── public/
│       ├── index.php          # PHP logic and HTML
│       └── assets/style.css   # Styling
└── README.md
```

VS Code's Explorer shows the app files and this README. Installed PHP/MySQL files, launch helpers, and unused Composer dependencies are hidden to keep the view focused. They remain on disk, and Ctrl+Shift+B still starts the app. To show them again, edit `files.exclude` in `.vscode/settings.json` (open it with Ctrl+P).

## How to run it

PHP and MySQL have now been installed and configured on this computer. Your tasks table is ready, and `src/config.php` contains the local app account details.

Open the `php/todo-list` folder in VS Code and press **Ctrl+Shift+B**. The task starts MySQL if needed, then starts the PHP server on the next available port (typically `http://localhost:8000`, or a higher port if 8000 is already occupied). Keep its terminal open. Other projects can go in their own folders beside `todo-list` inside `php`.

You can also run this from the project terminal:

```powershell
powershell -ExecutionPolicy Bypass -File .vscode/start.ps1
```

`.vscode/start.ps1` and `.vscode/tasks.json` are launch helpers; focus on the four app files above while learning PHP. PHP, MySQL, and this project's database are in the `.runtime` folder. The launcher uses those local files without relying on AppData settings. The app's database account can only read and change tasks in this database. Its generated password is in `src/config.php`. Both `src/config.php` and `.runtime` are excluded from Git; the database in `.runtime/mysql-data` contains your saved tasks, so keep it.

The steps below are for setting up a different computer. You do not need to repeat them here.

You need PHP with the `pdo_mysql` extension and a running MySQL server. PHP and MySQL are separate programs: starting PHP does not start MySQL.

### 1. Create the database

Start MySQL. If you use XAMPP, start MySQL in its Control Panel. To use XAMPP's phpMyAdmin, start Apache too.

In phpMyAdmin, open the **Import** tab, choose `src/schema.sql`, and import it. The SQL file creates a database named `taskflow` and a table named `tasks`.

### 2. Check your connection details

Open `src/config.php` and set these values to match your MySQL installation:

```php
$host = '127.0.0.1';
$database = 'taskflow';
$username = 'root';
$password = '';
```

An empty password only works if your local MySQL account has no password.

### 3. Start the PHP server

Open a terminal in this project folder and run:

```powershell
php -S localhost:8000 -t src/public
```

Keep that terminal open, then visit the URL the server prints (usually [http://localhost:8000](http://localhost:8000)). If 8000 is already occupied, use the next free port instead. Press **Ctrl+C** in the terminal when you want to stop the server.

If your terminal says `php` is not recognized, use the full path to PHP. For the WinGet PHP installation on this computer:

```powershell
& "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" -d extension=pdo_mysql -S localhost:8000 -t src/public
```

The `-t src/public` part tells PHP which folder contains the page to serve. You do not open `index.php` by double-clicking it; the PHP server must run it.

## What happens when you add a task?

1. You type a title and click **Add task**.
2. The HTML form sends its fields to `index.php` using a POST request.
3. PHP reads those fields through `$_POST` and checks the title.
4. PDO runs an `INSERT` query to save the title in MySQL.
5. PHP redirects back to the page and reads the saved tasks.
6. A `foreach` loop displays each task as HTML.

Completing and deleting tasks follow the same pattern, using different SQL queries. The page reloads after each successful change.

## Where CRUD happens

CRUD stands for **Create, Read, Update, Delete**. All four queries are in `src/public/index.php`.

| Action | SQL used | Meaning |
| --- | --- | --- |
| Create | `INSERT INTO tasks (title) VALUES (?)` | Save a new task. |
| Read | `SELECT * FROM tasks ORDER BY completed ASC, id DESC` | Get tasks, with unfinished tasks first. |
| Update | `UPDATE tasks SET completed = 1 WHERE id = ?` | Mark one task complete. |
| Delete | `DELETE FROM tasks WHERE id = ?` | Remove one task. |

Each task has an `id` (its unique number), a `title`, and a `completed` value. `0` means unfinished; `1` means complete. The hidden `id` input tells PHP which task a button belongs to.

The `?` in a query is a placeholder. For example:

```php
$statement = $pdo->prepare('INSERT INTO tasks (title) VALUES (?)');
$statement->execute([$title]);
```

`prepare()` sets up the query. `execute()` supplies the title separately, so user input is treated as data rather than SQL code.

## The parts that may look unfamiliar

Some lines are basic safeguards. Learn the CRUD flow first, then return to these:

- **`session_start()` and the token:** remember a random form token between requests. PHP checks it when a form is submitted to help prevent another website from submitting changes to your list.
- **`htmlspecialchars()`:** displays a task title as text, even if someone types HTML into it.
- **`filter_var()`:** checks that a task ID is an integer.
- **`header('Location: index.php', true, 303)`:** loads the page through a new GET request, so refreshing does not repeat the last form submission.
- **`<?= ... ?>`:** short PHP syntax for printing a value inside HTML.

The forms use `$_POST` because they change data. This version has no filters or URL parameters, so it does not need `$_GET` yet.

## Try it yourself

1. Add a task called `Learn PHP forms`.
2. Refresh the page and check that it remains.
3. Click **Complete** and check that its title is crossed out.
4. Click **Delete** and check that it disappears.
5. Try submitting an empty task; it should not be saved.

For practice, first change the heading in `index.php`, then change a button color in `style.css`. Once you understand the four queries, try adding a due date as your next feature.

## Common problems

- **`npm run dev` cannot find `package.json`:** use the PHP server command above. This project does not use npm.
- **Could not connect to MySQL:** check that MySQL is running, import the SQL file, and verify `src/config.php`. The earlier SQLite preview is no longer used.
- **Port 8000 is already in use:** stop the old server with Ctrl+C, or use `php -S localhost:8001 -t src/public` and open port 8001 instead.
- **Invalid form token:** refresh the page and submit the form again.

If you imported the earlier database schema, it still works; no need to delete existing tasks.

This is a local learning project with one shared task list. It has no login or separate lists for different users.
