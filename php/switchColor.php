<?php

class ColorTheme
{
    private $c_themes;

    private $cur_theme = 'light';

    public function __construct()
    {
        // цветовые схемы
        $this->c_themes = array (   
            'light' => array (
                'name'       => 'светлая',
                'text'       => '#000000',
                'background' => '#ffffff'   
            ),
            'dark' => array (
                'name'       => 'темная',
                'text'       => '#ffffff',
                'background' => '#000000'   
            ),
            'contast' => array (
                'name'       => 'контрастная',
                'text'       => '#ffff00',
                'background' => '#000000'   
            )
        );

        if (isset($_COOKIE['our_theme']) && array_key_exists($_COOKIE['our_theme'], $this->c_themes))
        {
            $this->cur_theme = $_COOKIE['our_theme'];
            echo $_COOKIE['our_theme'];
        }
    }

    public function switchTheme ($theme)
    {
        if (array_key_exists($theme, $this->c_themes))
        {
            $this->cur_theme = $theme;
            setcookie('our_theme',$theme,time() + (24*3600), "/"); // cookie on 1 day  
        }
        else
            return false;
        return true;
    }

    public function getCurTheme ()
    {
        return $this->c_themes[$this->cur_theme];
    }

    public function getThemes ()
    {
        return array_keys($this->c_themes);
    }
}

$colorTheme = new ColorTheme();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['color_theme'])) 
{
    $colorTheme->switchTheme($_POST['color_theme']);
    header("Location: " . $_SERVER['PHP_SELF']);    // обновить страницу
    exit();    
}

$currentTheme = $colorTheme->getCurTheme();
$allThemes = $colorTheme->getThemes();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            color:              <?php echo htmlspecialchars($currentTheme['text']); ?>;
            background-color:   <?php echo htmlspecialchars($currentTheme['background']); ?>;
        }           
    </style>
</head>
<body>
<h2>Color switcher!</h2>

<form method="post">
    <laber for="color_theme">Select theme:</label>
    <select name="color_theme" id="color_theme">
        <?php foreach($allThemes as $theme): ?>
        <option value="<?php htmlspecialchars($theme) ?>" <?php echo ($theme === $colorTheme->getCurTheme()['name']) ? 'selected' : ''; ?>>
        <?php echo $theme; ?> 
        </option>
        <?php endforeach; ?>   
    </select>
    <br>
    <button type="submit">Apply</button>    
</form>    

<p>Current theme: <?php echo $colorTheme->getCurTheme()['name']  ?> </p>

</body>
</html>