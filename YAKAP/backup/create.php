<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$backupDir = __DIR__ . '/files';
if (!is_dir($backupDir)) mkdir($backupDir, 0750, true);

$filename = 'yakap_backup_' . date('Ymd_His') . '.sql';
$path = $backupDir . '/' . $filename;

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$out = "-- YAKAP database backup\n-- Generated: ".date('Y-m-d H:i:s')."\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
    $out .= "DROP TABLE IF EXISTS `$table`;\n".$create['Create Table'].";\n\n";
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_NUM);
    foreach ($rows as $row) {
        $values = array_map(function($v) use ($pdo) {
            return $v === null ? 'NULL' : $pdo->quote((string)$v);
        }, $row);
        $out .= "INSERT INTO `$table` VALUES (".implode(',', $values).");\n";
    }
    $out .= "\n";
}
$out .= "SET FOREIGN_KEY_CHECKS=1;\n";
file_put_contents($path, $out);

$size = filesize($path);
$stmt = $pdo->prepare("INSERT INTO backup_records(filename, file_size, created_by) VALUES(?,?,?)");
$stmt->execute([$filename,$size,$_SESSION['user_id']]);

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="'.$filename.'"');
readfile($path);
exit;
