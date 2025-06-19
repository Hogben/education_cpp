<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h2>SQL Query executor</h2>
    <form method="post">
    <textarea name='sql_text' placeholder='введите sql запрос для воборки данных'><?php echo isset($_POST['sql_text']) ? htmlspecialchars($_POST['sql_text']) : '' ?></textarea><br>
    <button type='submit' name='run_query'>Run</button>
    </form>
    <?php
    if (isset($_POST['run_query']) && !empty($_POST['sql_text']))
    {

        $sql = trim($_POST['sql_text']);
        if (!$sql)
        {
            echo '<p>Запрос не может быть пустым.</p>';
            exit;
        }    
        if (mb_convert_case(strtok($sql, " "), MB_CASE_UPPER, "UTF-8") === 'SELECT' && strpos($sql, ';') === false)
        {
            $host = 'localhost'; 
            $db = 'test';
            $u_name = 'akim';
            $u_pass = 'fGUA89o6q1ANoh5n';

            try 
            {
                //-- create connection
                $conn = new PDO("mysql:host=$host; dbname=$db; charset=utf8", $u_name, $u_pass);
                //-- create error hook
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                //                     echo '<>';
                $res = $conn->query($sql);
                if ($res->columnCount() > 0)
                {
                    echo '<h3>Результат запроса:</h3>';
                    echo '<table>';
                    echo '<tr>';
                    for ($i = 0; $i < $res->columnCount(); $i++)
                    {
                        $col = $res->getColumnMeta($i);
                        echo '<th>'.htmlspecialchars($col['name']).'</th>';
                    }
                    echo '</tr>';
                    while ($row = $res->fetch(PDO::FETCH_ASSOC))
                    {
                        echo '<tr>';
                        foreach($row as $cell)
                        {
                            echo '<td>'.htmlspecialchars($cell).'</td>';    
                        }
                        echo '</tr>';
                    }    
                    echo '</table>';
                }

            }
            catch (PDOException $e)
            {
                echo 'Connection error: '.$e->getMessage();
            }
        }
        else
        {
            echo '<p>Запрос должен начинаться словом select.</p>';
            exit;
        }
    }    
    ?>
</body>
</html>