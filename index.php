<?php session_start();

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
      margin: 0;
      padding: 0;
    }

    :root {
      background: #fff;
      color: #000;
      font: normal normal 12px/18px sans-serif;
      padding: 12px
    }

    button,
    input,
    select,
    textarea {
      padding: 4px;
    }

    button {
      cursor: pointer;
      padding-left: 8px;
      padding-right: 8px;
    }

    select {
      cursor: pointer;
    }

    nav,
    p {
      display: block;
      margin: 12px 0;
    }

    table {
      border-collapse: collapse;
      table-layout: fixed;
      width: 100%;
    }

    td,
    th {
      padding: 6px;
      text-align: left;
      vertical-align: top;
    }

    table[border] td,
    table[border] th {
      border: 1px solid #000;
    }

    table div + div {
      margin-top: 6px;
    }

    </style>
  </head>
  <body>
    <?php if (isset($_SESSION['alert'])): ?>
      <p>
        <?= $_SESSION['alert']; ?>
      </p>
    <?php endif; ?>
    <?php if (isset($_GET['task'])): ?>
      <?php $task = $_GET['task']; ?>
      <form action="<?= $p; ?>/index.php?task=<?= $task; ?>" method="post">
        <?php if ('create' === $task): ?>
          <?php require __DIR__ . '/form/create.php'; ?>
        <?php elseif ('update' === $task): ?>
        <?php elseif ('delete' === $task): ?>
        <?php endif; ?>
        <input name="task" type="hidden" value="<?= $task; ?>">
      </form>
    <?php else: ?>
      <?php if ($base->querySingle("SELECT count(*) FROM sqlite_master WHERE type='table'")): ?>
        <table border="1">
          <thead>
            <tr>
              <th>
                All Tables
              </th>
            </tr>
          </thead>
          <tbody>
            <?php $rows = $base->query("SELECT name FROM sqlite_master WHERE type='table'"); ?>
            <?php while ($row = $rows->fetchArray(SQLITE3_NUM)): ?>
              <tr>
                <td>
                  <a href="<?= $p; ?>?table=<?= $row[0]; ?>&amp;task=update">
                    <?= $row[0]; ?>
                  </a>
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
    <nav>
      <?php if (isset($_GET['task'])): ?>
        <a href="<?= $p; ?>">All Tables</a>
      <?php else: ?>
        <a href="<?= $p; ?>?task=create">New Table</a>
      <?php endif; ?>
    </nav>
  </body>
</html>
<?php unset($_SESSION['alert']); ?>