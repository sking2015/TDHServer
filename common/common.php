<?php

/**
 * 从 CSV 文件中按列名查找匹配的行
 *
 * @param string $filename CSV 文件路径
 * @param string $columnName 要匹配的列名（来自 CSV 第一行）
 * @param string $targetValue 要查找的值
 * @return array|null 返回匹配行的关联数组（key 为列名），未找到则返回 null
 */
function findCsvRowByColumn($filename, $columnName, $targetValue)
{
    if (!file_exists($filename)) {
        throw new Exception("文件不存在: $filename");
    }

    if (($handle = fopen($filename, "r")) === false) {
        throw new Exception("无法打开文件: $filename");
    }

    // 读取首行（表头）
    $firstLine = fgets($handle);
    if ($firstLine === false) {
        fclose($handle);
        return null;
    }

    // 去除 BOM
    $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
    // 将首行重新用 fgetcsv 解析（因为 fgets 只是取文本）
    $headers = str_getcsv(trim($firstLine));

    // 检查列名是否存在
    if (!in_array($columnName, $headers)) {
        fclose($handle);
        throw new Exception("列名不存在: $columnName");
    }

    $colIndex = array_search($columnName, $headers);

    // 从第二行开始逐行读取
    while (($row = fgetcsv($handle)) !== false) {
        // 跳过空行
        if (count($row) == 1 && trim($row[0]) === '') continue;

        // 检查是否匹配
        if (isset($row[$colIndex]) && $row[$colIndex] == $targetValue) {
            fclose($handle);
            // 返回关联数组
            return array_combine($headers, $row);
        }
    }

    fclose($handle);
    return null;
}
