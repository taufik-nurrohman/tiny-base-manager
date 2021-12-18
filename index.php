<?php session_start();

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('html_errors', 1);

$p = trim(strtr(strtr(__DIR__ . '/', "\\", '/'), [strtr($_SERVER['DOCUMENT_ROOT'], "\\", '/') => '/']), '/');
$p = "" !== $p ? '/' . $p . '/index.php' : '/index.php';

if (!is_file(__DIR__ . '/table.db')) {
    $_SESSION['alert'] = 'Table does not exist. Attempt to create one!';
}

try {
    $base = new SQLite3(__DIR__ . '/table.db');
} catch (Exception $e) {
    $base = (object) [];
    $_SESSION['alert'] = (string) $e;
}

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $task = $_POST['task'];
    require __DIR__ . '/task/' . $task . '.php';
} else {
    if (isset($_GET['task']) && 'list' === $_GET['task']) {
        header('location: ' . $p);
        exit;
    }
}

?>
<!DOCTYPE html>
<html dir="ltr">
  <head>
    <meta content="width=device-width">
    <meta charset="utf-8">
    <title>Table Management System</title>
    <style>

    * {
      box-sizing: border-box;
    }

    :root {
      background: #fff;
      color: #000;
      font-family: 'times new roman', serif;
    }

    button {
      cursor: pointer;
    }

    select {
      cursor: pointer;
    }

    th, td {
      text-align: left;
      vertical-align: top;
    }

    body > p:first-child {
      background: #ff0;
      padding: .35em .5em;
    }

    </style>
  </head>
  <body>
    <?php if (isset($_SESSION['alert'])): ?>
      <p>
        <?= $_SESSION['alert']; ?>
      </p>
    <?php endif; ?>
    <form action="<?= $p; ?>" method="get">
      <?php if (isset($_GET['task'])): ?>
        <button name="task" type="submit" value="list">
          List
        </button>
      <?php else: ?>
        <button name="task" type="submit" value="create">
          Create
        </button>
      <?php endif; ?>
    </form>
    <hr>
    <form action="<?= $p; ?>?task=<?= $task_default = ($task = $_GET['task'] ?? null) ?? 'create'; ?>" enctype="multipart/form-data" method="post">
      <?php if ($task): ?>
        <?php if ('create' === $task): ?>
          <?php require __DIR__ . '/form/create.php'; ?>
        <?php elseif ('update' === $task): ?>
          <?php require __DIR__ . '/form/update.php'; ?>
        <?php endif; ?>
      <?php else: ?>
        <?php if ($base->querySingle("SELECT count(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")): ?>
          <table border="1">
            <thead>
              <tr>
                <th>
                  Tables
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php $rows = $base->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"); ?>
              <?php while ($row = $rows->fetchArray(SQLITE3_NUM)): ?>
                <tr>
                  <td>
                    <a href="<?= $p; ?>?table=<?= $row[0]; ?>&amp;task=update">
                      <?= $row[0]; ?>
                    </a>
                  </td>
                  <td>
                    <button name="drop" type="submit" value="<?= $row[0]; ?>">
                      Drop
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>
            No tables yet.
          </p>
        <?php endif; ?>
      <?php endif; ?>
      <input name="task" type="hidden" value="<?= $task_default; ?>">
    </form>
  </body>
</html>
<?php unset($_SESSION['alert']); ?>