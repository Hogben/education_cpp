<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Наши матрицы</title>
    <style>
        .matrix { border-collapse: collapse; margin: 20px 0; }
        .matrix th, .matrix td { padding: 8px; text-align: center; }
    </style>
</head>
<body>
    <h2>Наши прекрасные матрицы</h2>            
    <form method="POST" action="">
        <div class="matrix">
            <label for="rows">Введите количество строк:</label>
            <input type="number" id="rows" name="rows" value="<?php echo $_POST['rows'] ?? 4; ?>" min="1"><br>
            
            <label for="cols">Введите количество столбцов:</label>
            <input type="number" id="cols" name="cols" value="<?php echo $_POST['cols'] ?? 4; ?>" min="1"><br>
            
            <label>Тип подписей строк:</label>
            <input type="radio" name="rowTypeLabel" value="numeric" <?php echo (!isset($_POST['rowTypeLabel']) || $_POST['rowTypeLabel'] === 'numeric') ? 'checked' : ''; ?>> Числовые
            <input type="radio" name="rowTypeLabel" value="alpha" <?php echo (isset($_POST['rowTypeLabel']) && $_POST['rowTypeLabel'] === 'alpha') ? 'checked' : ''; ?>> Буквенные<br>
            
            <label>Тип подписей столбцов:</label>
            <input type="radio" name="colTypeLabel" value="numeric" <?php echo (!isset($_POST['colTypeLabel']) || $_POST['colTypeLabel'] === 'numeric') ? 'checked' : ''; ?>> Числовые
            <input type="radio" name="colTypeLabel" value="alpha" <?php echo (isset($_POST['colTypeLabel']) && $_POST['colTypeLabel'] === 'alpha') ? 'checked' : ''; ?>> Буквенные<br>
            
            <label>
                <input type="checkbox" name="showRowLabel" value="1" <?php echo (isset($_POST['showRowLabel']) || ($_SERVER['REQUEST_METHOD'] !== 'POST')) ? 'checked' : ''; ?>>
                Показывать подписи строк
            </label><br>

            <label>
                <input type="checkbox" name="showColLabel" value="1" <?php echo (isset($_POST['showColLabel']) || ($_SERVER['REQUEST_METHOD'] !== 'POST')) ? 'checked' : ''; ?>>
                Показывать подписи столбцов
            </label><br>            

            <button type="submit">Нарисуй матрицу</button>
        </div>
    </form>
    
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'matrix.php';
    
    $rows = intval($_POST['rows'] ?? 4);
    $cols = intval($_POST['cols'] ?? 4);
    $rowTypeLabel = $_POST['rowTypeLabel'] ?? 'numeric';
    $colTypeLabel = $_POST['colTypeLabel'] ?? 'numeric';
    $showRowLabel = isset($_POST['showRowLabel']);
    $showColLabel = isset($_POST['showColLabel']);

    $matrix = new Matrix($rows, $cols, $rowTypeLabel, $colTypeLabel);
    $matrix->setShowColsLabel($showColLabel);
    $matrix->setShowRowsLabel($showRowLabel);

    if ($colTypeLabel === 'alpha')
    {
        //-----------  1   2   3   4   5                   10
        $cyr_label = ['А','Б','В','Г','Д','Е','Ж','З','И','К'];
        while (true)
        {
            for ($i = 0; $i < count($cyr_label); $i++)
            {
                if (!$matrix->setColLabel($i, $cyr_label[$i]))  break;
            }
            break;
        }
        
    }
    
    // Выводим матрицу
    echo $matrix->make();
}    
?>
</body>
</html>